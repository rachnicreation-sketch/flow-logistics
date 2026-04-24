<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Auth;
use App\Core\Model;
use PDO;
use RuntimeException;

final class Ticket extends Model
{
    protected string $table = 'tickets';
    private static bool $schemaReady = false;

    public function __construct()
    {
        parent::__construct();
        $this->ensureSchema();
    }

    public function createTicket(array $data): int
    {
        $companyId = $this->resolveCompanyId($data['company_id'] ?? null);

        return $this->insert([
            'company_id' => $companyId,
            'ticket_number' => $data['ticket_number'],
            'title' => $data['title'],
            'description' => $data['description'],
            'module_name' => $data['module_name'] ?? null,
            'status' => $data['status'] ?? 'open',
            'priority' => $data['priority'] ?? 'medium',
            'reporter_id' => $data['reporter_id'] ?? Auth::id(),
            'assigned_to' => $data['assigned_to'] ?? null,
            'due_at' => $data['due_at'] ?? null,
            'closed_at' => null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => null,
        ]);
    }

    public function listTickets(): array
    {
        $sql = 'SELECT t.*,
                       reporter.name AS reporter_name,
                       assignee.name AS assignee_name,
                       COALESCE(tc.comment_count, 0) AS comment_count
                FROM tickets t
                LEFT JOIN users reporter ON reporter.id = t.reporter_id
                LEFT JOIN users assignee ON assignee.id = t.assigned_to
                LEFT JOIN (
                    SELECT ticket_id, COUNT(*) AS comment_count
                    FROM ticket_comments
                    GROUP BY ticket_id
                ) tc ON tc.ticket_id = t.id';

        if (!$this->isSuperAdmin()) {
            $sql .= ' WHERE t.company_id = :company_id';
        }

        $sql .= ' ORDER BY FIELD(t.priority, "urgent","high","medium","low"), t.id DESC';
        $stmt = $this->db->prepare($sql);
        if (!$this->isSuperAdmin()) {
            $stmt->bindValue(':company_id', $this->currentCompanyId(), PDO::PARAM_INT);
        }
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function commentsByTicket(int $ticketId): array
    {
        $sql = 'SELECT tc.*, u.name AS user_name
                FROM ticket_comments tc
                LEFT JOIN users u ON u.id = tc.user_id
                WHERE tc.ticket_id = :ticket_id';
        if (!$this->isSuperAdmin()) {
            $sql .= ' AND tc.company_id = :company_id';
        }
        $sql .= ' ORDER BY tc.id ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':ticket_id', $ticketId, PDO::PARAM_INT);
        if (!$this->isSuperAdmin()) {
            $stmt->bindValue(':company_id', $this->currentCompanyId(), PDO::PARAM_INT);
        }
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function addComment(int $ticketId, string $comment): int
    {
        $ticket = $this->find($ticketId);
        if (!$ticket) {
            throw new RuntimeException('Ticket introuvable.');
        }

        $stmt = $this->db->prepare(
            'INSERT INTO ticket_comments (company_id, ticket_id, user_id, comment, created_at)
             VALUES (:company_id, :ticket_id, :user_id, :comment, :created_at)'
        );
        $stmt->execute([
            'company_id' => $ticket['company_id'],
            'ticket_id' => $ticketId,
            'user_id' => Auth::id(),
            'comment' => $comment,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->touch($ticketId);
        return (int) $this->db->lastInsertId();
    }

    public function assignTo(int $ticketId, ?int $userId): bool
    {
        $ok = $this->updateById($ticketId, [
            'assigned_to' => $userId,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $ok;
    }

    public function updateTicketStatus(int $ticketId, string $status): bool
    {
        $allowed = ['open', 'in_progress', 'resolved', 'closed'];
        if (!in_array($status, $allowed, true)) {
            throw new RuntimeException('Statut invalide.');
        }

        $data = [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($status === 'closed') {
            $data['closed_at'] = date('Y-m-d H:i:s');
        }
        if ($status === 'open' || $status === 'in_progress') {
            $data['closed_at'] = null;
        }

        return $this->updateById($ticketId, $data);
    }

    public function updateTicketPriority(int $ticketId, string $priority): bool
    {
        $allowed = ['low', 'medium', 'high', 'urgent'];
        if (!in_array($priority, $allowed, true)) {
            throw new RuntimeException('Priorite invalide.');
        }

        return $this->updateById($ticketId, [
            'priority' => $priority,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function stats(): array
    {
        $sql = 'SELECT status, COUNT(*) AS total
                FROM tickets';
        if (!$this->isSuperAdmin()) {
            $sql .= ' WHERE company_id = :company_id';
        }
        $sql .= ' GROUP BY status';

        $stmt = $this->db->prepare($sql);
        if (!$this->isSuperAdmin()) {
            $stmt->bindValue(':company_id', $this->currentCompanyId(), PDO::PARAM_INT);
        }
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $out = ['open' => 0, 'in_progress' => 0, 'resolved' => 0, 'closed' => 0];
        foreach ($rows as $row) {
            $status = (string) $row['status'];
            $out[$status] = (int) $row['total'];
        }

        return $out;
    }

    private function touch(int $ticketId): void
    {
        $stmt = $this->db->prepare(
            'UPDATE tickets SET updated_at = :updated_at WHERE id = :id'
        );
        $stmt->execute([
            'updated_at' => date('Y-m-d H:i:s'),
            'id' => $ticketId,
        ]);
    }

    private function resolveCompanyId(mixed $explicit): int
    {
        if (is_numeric($explicit) && (int) $explicit > 0) {
            return (int) $explicit;
        }

        if ($this->currentCompanyId() !== null) {
            return (int) $this->currentCompanyId();
        }

        $fallback = (int) $this->db->query('SELECT id FROM companies ORDER BY id ASC LIMIT 1')->fetchColumn();
        if ($fallback <= 0) {
            throw new RuntimeException('Aucune entreprise disponible pour creer un ticket.');
        }

        return $fallback;
    }

    private function ensureSchema(): void
    {
        if (self::$schemaReady) {
            return;
        }

        $this->db->exec(
            'CREATE TABLE IF NOT EXISTS tickets (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                company_id BIGINT UNSIGNED NOT NULL,
                ticket_number VARCHAR(50) NOT NULL,
                title VARCHAR(190) NOT NULL,
                description TEXT NOT NULL,
                module_name VARCHAR(120) NULL,
                status ENUM("open","in_progress","resolved","closed") NOT NULL DEFAULT "open",
                priority ENUM("low","medium","high","urgent") NOT NULL DEFAULT "medium",
                reporter_id BIGINT UNSIGNED NULL,
                assigned_to BIGINT UNSIGNED NULL,
                due_at DATETIME NULL,
                closed_at DATETIME NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NULL,
                CONSTRAINT fk_ticket_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
                CONSTRAINT fk_ticket_reporter FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE SET NULL,
                CONSTRAINT fk_ticket_assignee FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
                UNIQUE KEY uq_ticket_company_number (company_id, ticket_number),
                INDEX idx_ticket_company_status (company_id, status),
                INDEX idx_ticket_assignee (assigned_to)
            ) ENGINE=InnoDB'
        );

        $this->db->exec(
            'CREATE TABLE IF NOT EXISTS ticket_comments (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                company_id BIGINT UNSIGNED NOT NULL,
                ticket_id BIGINT UNSIGNED NOT NULL,
                user_id BIGINT UNSIGNED NULL,
                comment TEXT NOT NULL,
                created_at DATETIME NOT NULL,
                CONSTRAINT fk_ticket_comment_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
                CONSTRAINT fk_ticket_comment_ticket FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
                CONSTRAINT fk_ticket_comment_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
                INDEX idx_ticket_comment_ticket (ticket_id),
                INDEX idx_ticket_comment_company (company_id)
            ) ENGINE=InnoDB'
        );

        self::$schemaReady = true;
    }
}
