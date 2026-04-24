<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

final class ApiToken extends Model
{
    protected string $table = 'api_tokens';

    public function issue(int $companyId, int $userId, int $ttlHours = 24): string
    {
        $token = bin2hex(random_bytes(32));
        $stmt = $this->db->prepare(
            'INSERT INTO api_tokens (company_id, user_id, token, expires_at, created_at)
             VALUES (:company_id, :user_id, :token, :expires_at, :created_at)'
        );
        $stmt->execute([
            'company_id' => $companyId,
            'user_id' => $userId,
            'token' => hash('sha256', $token),
            'expires_at' => date('Y-m-d H:i:s', strtotime("+{$ttlHours} hours")),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return $token;
    }

    public function resolveUser(string $plainToken): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT at.*, u.name, u.email, u.role_id
             FROM api_tokens at
             INNER JOIN users u ON u.id = at.user_id
             WHERE at.token = :token AND at.expires_at > NOW()
             LIMIT 1'
        );
        $stmt->execute(['token' => hash('sha256', $plainToken)]);
        return $stmt->fetch() ?: null;
    }
}

