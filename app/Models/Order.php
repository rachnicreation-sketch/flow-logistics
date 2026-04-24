<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Auth;
use App\Core\Model;
use PDO;

final class Order extends Model
{
    protected string $table = 'orders';

    public function createOrder(array $header, array $items): int
    {
        $this->db->beginTransaction();
        try {
            $orderId = $this->insert([
                'company_id' => $this->currentCompanyId(),
                'customer_id' => $header['customer_id'],
                'reference' => $header['reference'],
                'status' => $header['status'] ?? 'pending',
                'invoice_number' => $header['invoice_number'],
                'delivery_address' => $header['delivery_address'] ?? null,
                'total_amount' => $header['total_amount'],
                'created_by' => Auth::id(),
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $stmt = $this->db->prepare(
                'INSERT INTO order_items (company_id, order_id, product_id, quantity, unit_price, total_price)
                 VALUES (:company_id, :order_id, :product_id, :quantity, :unit_price, :total_price)'
            );
            foreach ($items as $item) {
                $stmt->execute([
                    'company_id' => $this->currentCompanyId(),
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['quantity'] * $item['unit_price'],
                ]);
            }

            $this->db->commit();
            return $orderId;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function listOrders(): array
    {
        $sql = 'SELECT o.*, c.name AS customer_name, u.name AS creator_name
                FROM orders o
                INNER JOIN customers c ON c.id = o.customer_id
                LEFT JOIN users u ON u.id = o.created_by';
        if (!$this->isSuperAdmin()) {
            $sql .= ' WHERE o.company_id = :company_id';
        }
        $sql .= ' ORDER BY o.id DESC';
        $stmt = $this->db->prepare($sql);
        if (!$this->isSuperAdmin()) {
            $stmt->bindValue(':company_id', $this->currentCompanyId(), PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function items(int $orderId): array
    {
        $sql = 'SELECT oi.*, p.name AS product_name, p.sku
                FROM order_items oi
                INNER JOIN products p ON p.id = oi.product_id
                WHERE oi.order_id = :order_id';
        if (!$this->isSuperAdmin()) {
            $sql .= ' AND oi.company_id = :company_id';
        }
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':order_id', $orderId, PDO::PARAM_INT);
        if (!$this->isSuperAdmin()) {
            $stmt->bindValue(':company_id', $this->currentCompanyId(), PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function updateStatus(int $orderId, string $status): bool
    {
        return $this->updateById($orderId, [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function pendingCount(): int
    {
        $sql = 'SELECT COUNT(*) FROM orders WHERE status IN ("pending","validated","prepared")';
        if (!$this->isSuperAdmin()) {
            $sql .= ' AND company_id = :company_id';
        }
        $stmt = $this->db->prepare($sql);
        if (!$this->isSuperAdmin()) {
            $stmt->bindValue(':company_id', $this->currentCompanyId(), PDO::PARAM_INT);
        }
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }
}

