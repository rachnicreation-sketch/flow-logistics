<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Auth;
use App\Core\Model;
use PDO;

final class StockMovement extends Model
{
    protected string $table = 'stock_movements';

    public function add(array $data): int
    {
        return $this->insert([
            'company_id' => $this->resolveCompanyId(),
            'product_id' => $data['product_id'],
            'warehouse_id' => $data['warehouse_id'],
            'location_id' => $data['location_id'] ?? null,
            'user_id' => Auth::id(),
            'type' => $data['type'],
            'method' => $data['method'] ?? 'FIFO',
            'quantity' => $data['quantity'],
            'reference_type' => $data['reference_type'] ?? null,
            'reference_id' => $data['reference_id'] ?? null,
            'notes' => $data['notes'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function history(int $limit = 200): array
    {
        $sql = 'SELECT sm.*, p.name AS product_name, p.sku, w.name AS warehouse_name, u.name AS user_name
                FROM stock_movements sm
                INNER JOIN products p ON p.id = sm.product_id
                INNER JOIN warehouses w ON w.id = sm.warehouse_id
                LEFT JOIN users u ON u.id = sm.user_id';
        if (!$this->isSuperAdmin()) {
            $sql .= ' WHERE sm.company_id = :company_id';
        }
        $sql .= ' ORDER BY sm.id DESC LIMIT :limit_count';
        $stmt = $this->db->prepare($sql);
        if (!$this->isSuperAdmin()) {
            $stmt->bindValue(':company_id', $this->currentCompanyId(), PDO::PARAM_INT);
        }
        $stmt->bindValue(':limit_count', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}

