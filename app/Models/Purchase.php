<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Auth;
use App\Core\Model;
use PDO;

final class Purchase extends Model
{
    protected string $table = 'purchases';

    public function createPurchase(array $header, array $items): int
    {
        $this->db->beginTransaction();
        try {
            $id = $this->insert([
                'company_id' => $this->currentCompanyId(),
                'supplier_id' => $header['supplier_id'],
                'reference' => $header['reference'],
                'status' => $header['status'] ?? 'ordered',
                'expected_date' => $header['expected_date'] ?? null,
                'total_amount' => $header['total_amount'],
                'created_by' => Auth::id(),
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $stmt = $this->db->prepare(
                'INSERT INTO purchase_items (company_id, purchase_id, product_id, quantity, unit_price, total_price)
                 VALUES (:company_id, :purchase_id, :product_id, :quantity, :unit_price, :total_price)'
            );
            foreach ($items as $item) {
                $stmt->execute([
                    'company_id' => $this->currentCompanyId(),
                    'purchase_id' => $id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['quantity'] * $item['unit_price'],
                ]);
            }

            $this->db->commit();
            return $id;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function listPurchases(): array
    {
        $sql = 'SELECT p.*, s.name AS supplier_name, u.name AS creator_name
                FROM purchases p
                INNER JOIN suppliers s ON s.id = p.supplier_id
                LEFT JOIN users u ON u.id = p.created_by';
        if (!$this->isSuperAdmin()) {
            $sql .= ' WHERE p.company_id = :company_id';
        }
        $sql .= ' ORDER BY p.id DESC';
        $stmt = $this->db->prepare($sql);
        if (!$this->isSuperAdmin()) {
            $stmt->bindValue(':company_id', $this->currentCompanyId(), PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function items(int $purchaseId): array
    {
        $sql = 'SELECT pi.*, pr.name AS product_name, pr.sku
                FROM purchase_items pi
                INNER JOIN products pr ON pr.id = pi.product_id
                WHERE pi.purchase_id = :purchase_id';
        if (!$this->isSuperAdmin()) {
            $sql .= ' AND pi.company_id = :company_id';
        }
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':purchase_id', $purchaseId, PDO::PARAM_INT);
        if (!$this->isSuperAdmin()) {
            $stmt->bindValue(':company_id', $this->currentCompanyId(), PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function markReceived(int $purchaseId): bool
    {
        return $this->updateById($purchaseId, [
            'status' => 'received',
            'received_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }
}

