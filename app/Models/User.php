<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

final class User extends Model
{
    protected string $table = 'users';

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT u.*, r.slug AS role_slug
             FROM users u
             INNER JOIN roles r ON r.id = u.role_id
             WHERE u.email = :email
             LIMIT 1'
        );
        $stmt->execute(['email' => $email]);
        return $stmt->fetch() ?: null;
    }

    public function permissions(int $roleId): array
    {
        $stmt = $this->db->prepare(
            'SELECT p.slug
             FROM role_permissions rp
             INNER JOIN permissions p ON p.id = rp.permission_id
             WHERE rp.role_id = :role_id'
        );
        $stmt->execute(['role_id' => $roleId]);
        return array_column($stmt->fetchAll(), 'slug');
    }

    public function createUser(array $data): int
    {
        return $this->insert([
            'company_id' => $data['company_id'] ?? null,
            'role_id' => $data['role_id'],
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'is_active' => $data['is_active'] ?? 1,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function listUsers(): array
    {
        $sql = 'SELECT u.*, r.name AS role_name, r.slug AS role_slug, c.name AS company_name
                FROM users u
                INNER JOIN roles r ON r.id = u.role_id
                LEFT JOIN companies c ON c.id = u.company_id';
        if (!$this->isSuperAdmin()) {
            $sql .= ' WHERE u.company_id = :company_id';
        }
        $sql .= ' ORDER BY u.id DESC';
        $stmt = $this->db->prepare($sql);
        if (!$this->isSuperAdmin()) {
            $stmt->bindValue(':company_id', $this->currentCompanyId(), PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function touchLastLogin(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE users SET last_login_at = :last_login_at WHERE id = :id');
        $stmt->execute([
            'last_login_at' => date('Y-m-d H:i:s'),
            'id' => $id,
        ]);
    }

    public function setStatus(int $id, int $active): bool
    {
        return $this->updateById($id, [
            'is_active' => $active,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function updateUser(int $id, array $data): bool
    {
        $payload = [
            'role_id' => $data['role_id'],
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'is_active' => $data['is_active'] ?? 1,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if (array_key_exists('password', $data) && (string) $data['password'] !== '') {
            $payload['password_hash'] = password_hash((string) $data['password'], PASSWORD_DEFAULT);
        }

        if (array_key_exists('company_id', $data)) {
            $payload['company_id'] = $data['company_id'];
        }

        return $this->updateById($id, $payload);
    }

    public function emailExistsForAnotherUser(string $email, int $exceptId): bool
    {
        $sql = 'SELECT COUNT(*) FROM users WHERE email = :email AND id <> :except_id';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':except_id', $exceptId, PDO::PARAM_INT);
        $stmt->execute();
        return (int) $stmt->fetchColumn() > 0;
    }

    public function deleteUser(int $id): bool
    {
        return $this->deleteById($id);
    }

    public function findWithRole(int $id): ?array
    {
        $sql = 'SELECT u.*, r.slug AS role_slug, r.name AS role_name
                FROM users u
                INNER JOIN roles r ON r.id = u.role_id
                WHERE u.id = :id';
        if (!$this->isSuperAdmin()) {
            $sql .= ' AND u.company_id = :company_id';
        }
        $sql .= ' LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        if (!$this->isSuperAdmin()) {
            $stmt->bindValue(':company_id', $this->currentCompanyId(), PDO::PARAM_INT);
        }
        $stmt->execute();
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
