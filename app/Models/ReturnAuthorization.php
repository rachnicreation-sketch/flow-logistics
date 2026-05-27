<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

final class ReturnAuthorization extends Model
{
    protected string $table = 'return_authorizations';

    public function listWithDetails(): array
    {
        $sql = 'SELECT ra.*,
                       c.name AS customer_name,
                       o.reference AS order_reference
                FROM return_authorizations ra
                INNER JOIN customers c ON c.id = ra.customer_id
                INNER JOIN orders o ON o.id = ra.order_id';

        if (!$this->isSuperAdmin()) {
            $sql .= ' WHERE ra.company_id = :company_id';
        }
        $sql .= ' ORDER BY ra.created_at DESC';

        $stmt = $this->db->prepare($sql);
        if (!$this->isSuperAdmin()) {
            $stmt->bindValue(':company_id', $this->currentCompanyId(), PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function createReturn(array $data, array $items): int
    {
        $id = $this->insert([
            'company_id' => $this->resolveCompanyId(),
            'order_id' => $data['order_id'],
            'customer_id' => $data['customer_id'],
            'return_number' => 'RET-' . date('Ymd') . '-' . random_int(1000, 9999),
            'reason' => $data['reason'],
            'status' => 'requested',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        foreach ($items as $item) {
            $pid = (int) ($item['product_id'] ?? 0);
            $qty = (float) ($item['quantity'] ?? 0);
            $cond = $item['condition_status'] ?? 'resellable';
            if ($pid > 0 && $qty > 0) {
                $this->db->prepare(
                    'INSERT INTO return_items (return_id, product_id, quantity, condition_status)
                     VALUES (:return_id, :product_id, :quantity, :condition_status)'
                )->execute([
                    'return_id' => $id,
                    'product_id' => $pid,
                    'quantity' => $qty,
                    'condition_status' => $cond
                ]);
            }
        }

        return $id;
    }

    public function updateStatus(int $id, string $status): bool
    {
        return $this->updateById($id, [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function detail(int $id): ?array
    {
        $sql = 'SELECT ra.*,
                       c.name AS customer_name,
                       o.reference AS order_reference
                FROM return_authorizations ra
                INNER JOIN customers c ON c.id = ra.customer_id
                INNER JOIN orders o ON o.id = ra.order_id
                WHERE ra.id = :id';

        if (!$this->isSuperAdmin()) {
            $sql .= ' AND ra.company_id = :company_id';
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

    public function items(int $returnId): array
    {
        $stmt = $this->db->prepare(
            'SELECT ri.*, p.name AS product_name, p.sku
             FROM return_items ri
             INNER JOIN products p ON p.id = ri.product_id
             WHERE ri.return_id = :return_id'
        );
        $stmt->bindValue(':return_id', $returnId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
