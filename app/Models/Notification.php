<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

final class Notification extends Model
{
    protected string $table = 'notifications';

    public function createForUser(?int $userId, string $type, string $title, string $message): int
    {
        return $this->insert([
            'company_id' => $this->currentCompanyId(),
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function forCurrentUser(int $limit = 12): array
    {
        $sql = 'SELECT * FROM notifications WHERE company_id = :company_id
                AND (user_id = :user_id OR user_id IS NULL)
                ORDER BY id DESC LIMIT :limit_count';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':company_id', $this->currentCompanyId(), PDO::PARAM_INT);
        $stmt->bindValue(':user_id', \App\Core\Auth::id(), PDO::PARAM_INT);
        $stmt->bindValue(':limit_count', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}

