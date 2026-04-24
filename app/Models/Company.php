<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

final class Company extends Model
{
    protected string $table = 'companies';

    public function create(array $data): int
    {
        return $this->insert([
            'name' => $data['name'],
            'code' => $data['code'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'status' => $data['status'] ?? 'active',
            'settings_json' => $data['settings_json'] ?? '{}',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function update(int $id, array $data): bool
    {
        return $this->updateById($id, [
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'status' => $data['status'] ?? 'active',
            'settings_json' => $data['settings_json'] ?? '{}',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function findByCode(string $code): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM companies WHERE code = :code LIMIT 1');
        $stmt->execute(['code' => $code]);
        return $stmt->fetch() ?: null;
    }

    public function stats(): array
    {
        return [
            'total' => (int) $this->db->query('SELECT COUNT(*) FROM companies')->fetchColumn(),
            'active' => (int) $this->db->query("SELECT COUNT(*) FROM companies WHERE status = 'active'")->fetchColumn(),
        ];
    }
}

