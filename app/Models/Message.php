<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Auth;
use App\Core\Model;
use PDO;
use RuntimeException;

final class Message extends Model
{
    protected string $table = 'messages';
    private static bool $schemaReady = false;

    public function __construct()
    {
        parent::__construct();
        $this->ensureSchema();
    }

    public function send(int $recipientId, string $subject, string $body): int
    {
        if ($recipientId <= 0) {
            throw new RuntimeException('Destinataire invalide.');
        }

        return $this->insert([
            'company_id' => $this->resolveCompanyId(),
            'sender_id' => Auth::id(),
            'recipient_id' => $recipientId,
            'subject' => $subject,
            'body' => $body,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'read_at' => null,
        ]);
    }

    public function inbox(int $limit = 200): array
    {
        $sql = 'SELECT m.*, s.name AS sender_name, r.name AS recipient_name
                FROM messages m
                LEFT JOIN users s ON s.id = m.sender_id
                LEFT JOIN users r ON r.id = m.recipient_id
                WHERE m.recipient_id = :recipient_id';

        if (!$this->isSuperAdmin()) {
            $sql .= ' AND m.company_id = :company_id';
        }

        $sql .= ' ORDER BY m.id DESC LIMIT :limit_count';

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':recipient_id', Auth::id(), PDO::PARAM_INT);
        if (!$this->isSuperAdmin()) {
            $stmt->bindValue(':company_id', $this->resolveCompanyId(), PDO::PARAM_INT);
        }
        $stmt->bindValue(':limit_count', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function sent(int $limit = 200): array
    {
        $sql = 'SELECT m.*, s.name AS sender_name, r.name AS recipient_name
                FROM messages m
                LEFT JOIN users s ON s.id = m.sender_id
                LEFT JOIN users r ON r.id = m.recipient_id
                WHERE m.sender_id = :sender_id';

        if (!$this->isSuperAdmin()) {
            $sql .= ' AND m.company_id = :company_id';
        }

        $sql .= ' ORDER BY m.id DESC LIMIT :limit_count';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':sender_id', Auth::id(), PDO::PARAM_INT);
        if (!$this->isSuperAdmin()) {
            $stmt->bindValue(':company_id', $this->resolveCompanyId(), PDO::PARAM_INT);
        }
        $stmt->bindValue(':limit_count', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function unreadCount(): int
    {
        $sql = 'SELECT COUNT(*)
                FROM messages
                WHERE recipient_id = :recipient_id AND is_read = 0';

        if (!$this->isSuperAdmin()) {
            $sql .= ' AND company_id = :company_id';
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':recipient_id', Auth::id(), PDO::PARAM_INT);
        if (!$this->isSuperAdmin()) {
            $stmt->bindValue(':company_id', $this->resolveCompanyId(), PDO::PARAM_INT);
        }
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function markAsRead(int $messageId): bool
    {
        $sql = 'UPDATE messages
                SET is_read = 1, read_at = :read_at
                WHERE id = :id AND recipient_id = :recipient_id';

        if (!$this->isSuperAdmin()) {
            $sql .= ' AND company_id = :company_id';
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':read_at', date('Y-m-d H:i:s'));
        $stmt->bindValue(':id', $messageId, PDO::PARAM_INT);
        $stmt->bindValue(':recipient_id', Auth::id(), PDO::PARAM_INT);
        if (!$this->isSuperAdmin()) {
            $stmt->bindValue(':company_id', $this->resolveCompanyId(), PDO::PARAM_INT);
        }
        return $stmt->execute();
    }



    private function ensureSchema(): void
    {
        if (self::$schemaReady) {
            return;
        }

        $this->db->exec(
            'CREATE TABLE IF NOT EXISTS messages (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                company_id BIGINT UNSIGNED NOT NULL,
                sender_id BIGINT UNSIGNED NULL,
                recipient_id BIGINT UNSIGNED NOT NULL,
                subject VARCHAR(190) NOT NULL,
                body TEXT NOT NULL,
                is_read TINYINT(1) NOT NULL DEFAULT 0,
                created_at DATETIME NOT NULL,
                read_at DATETIME NULL,
                CONSTRAINT fk_message_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
                CONSTRAINT fk_message_sender FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE SET NULL,
                CONSTRAINT fk_message_recipient FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_message_company_recipient (company_id, recipient_id, is_read),
                INDEX idx_message_sender (sender_id)
            ) ENGINE=InnoDB'
        );

        self::$schemaReady = true;
    }
}
