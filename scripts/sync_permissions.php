<?php

declare(strict_types=1);

use App\Config\Config;
use App\Core\Database;
use App\Core\Env;

$sessionDir = dirname(__DIR__) . '/logs/sessions';
if (!is_dir($sessionDir)) {
    @mkdir($sessionDir, 0777, true);
}
ini_set('session.save_path', $sessionDir);

require_once dirname(__DIR__) . '/app/bootstrap.php';

Env::load(dirname(__DIR__) . '/.env');
Config::load();

$pdo = Database::connection();

$permissions = [
    ['name' => 'Messagerie interne', 'slug' => 'messages.manage'],
    ['name' => 'Gerer ticketing', 'slug' => 'tickets.manage'],
];

foreach ($permissions as $permission) {
    $stmt = $pdo->prepare(
        'INSERT INTO permissions (name, slug)
         SELECT :name, :slug
         WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE slug = :slug_check)'
    );
    $stmt->execute([
        'name' => $permission['name'],
        'slug' => $permission['slug'],
        'slug_check' => $permission['slug'],
    ]);
}

$rolePermissions = [
    'messages.manage' => ['super_admin', 'dg', 'dm', 'company_admin', 'logistics_manager', 'storekeeper', 'driver'],
    'tickets.manage' => ['super_admin', 'dg', 'dm', 'company_admin', 'logistics_manager'],
];

foreach ($rolePermissions as $permissionSlug => $roleSlugs) {
    foreach ($roleSlugs as $roleSlug) {
        $stmt = $pdo->prepare(
            'INSERT INTO role_permissions (role_id, permission_id)
             SELECT r.id, p.id
             FROM roles r
             INNER JOIN permissions p ON p.slug = :permission_slug
             WHERE r.slug = :role_slug
               AND NOT EXISTS (
                   SELECT 1
                   FROM role_permissions rp
                   WHERE rp.role_id = r.id
                     AND rp.permission_id = p.id
               )'
        );
        $stmt->execute([
            'permission_slug' => $permissionSlug,
            'role_slug' => $roleSlug,
        ]);
    }
}

echo "Permissions synchronisees.\n";
