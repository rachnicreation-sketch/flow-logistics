<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Auth;
use App\Core\Model;
use PDO;

final class AuditLog extends Model
{
    protected string $table = 'audit_logs';

    public function add(string $action, string $module, ?int $entityId = null, array $meta = []): int
    {
        return $this->insert([
            'company_id' => Auth::companyId(),
            'user_id' => Auth::id(),
            'action' => $action,
            'module' => $module,
            'entity_id' => $entityId,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'metadata_json' => $meta ? json_encode($meta, JSON_UNESCAPED_UNICODE) : null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function recent(int $limit = 100): array
    {
        $sql = 'SELECT al.*, u.name AS user_name
                FROM audit_logs al
                LEFT JOIN users u ON u.id = al.user_id';
        if (!$this->isSuperAdmin()) {
            $sql .= ' WHERE al.company_id = :company_id';
        }
        $sql .= ' ORDER BY al.id DESC LIMIT :limit_count';
        $stmt = $this->db->prepare($sql);
        if (!$this->isSuperAdmin()) {
            $stmt->bindValue(':company_id', $this->currentCompanyId(), PDO::PARAM_INT);
        }
        $stmt->bindValue(':limit_count', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}

