<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

final class Stock extends Model
{
    protected string $table = 'stocks';

    public function upsert(
        int $productId,
        int $warehouseId,
        ?int $locationId,
        float $quantity
    ): void {
        $companyId = $this->currentCompanyId();
        $sql = 'SELECT id, quantity FROM stocks
                WHERE company_id = :company_id
                  AND product_id = :product_id
                  AND warehouse_id = :warehouse_id
                  AND ((location_id IS NULL AND :location_id IS NULL) OR location_id = :location_id)
                LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'company_id' => $companyId,
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'location_id' => $locationId,
        ]);
        $existing = $stmt->fetch();

        if ($existing) {
            $newQty = (float) $existing['quantity'] + $quantity;
            $update = $this->db->prepare(
                'UPDATE stocks SET quantity = :quantity, updated_at = :updated_at WHERE id = :id'
            );
            $update->execute([
                'quantity' => $newQty,
                'updated_at' => date('Y-m-d H:i:s'),
                'id' => $existing['id'],
            ]);
            return;
        }

        $insert = $this->db->prepare(
            'INSERT INTO stocks (company_id, product_id, warehouse_id, location_id, quantity, updated_at)
             VALUES (:company_id, :product_id, :warehouse_id, :location_id, :quantity, :updated_at)'
        );
        $insert->execute([
            'company_id' => $companyId,
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'location_id' => $locationId,
            'quantity' => $quantity,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function summary(): array
    {
        $sql = 'SELECT s.*, p.name AS product_name, p.sku, w.name AS warehouse_name, wl.label AS location_label
                FROM stocks s
                INNER JOIN products p ON p.id = s.product_id
                INNER JOIN warehouses w ON w.id = s.warehouse_id
                LEFT JOIN warehouse_locations wl ON wl.id = s.location_id';
        if (!$this->isSuperAdmin()) {
            $sql .= ' WHERE s.company_id = :company_id';
        }
        $sql .= ' ORDER BY s.updated_at DESC';
        $stmt = $this->db->prepare($sql);
        if (!$this->isSuperAdmin()) {
            $stmt->bindValue(':company_id', $this->currentCompanyId(), PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function totalQuantity(): float
    {
        $sql = 'SELECT COALESCE(SUM(quantity), 0) AS total_qty FROM stocks';
        if (!$this->isSuperAdmin()) {
            $sql .= ' WHERE company_id = :company_id';
        }
        $stmt = $this->db->prepare($sql);
        if (!$this->isSuperAdmin()) {
            $stmt->bindValue(':company_id', $this->currentCompanyId(), PDO::PARAM_INT);
        }
        $stmt->execute();
        return (float) ($stmt->fetch()['total_qty'] ?? 0);
    }

    public function detail(int $id): ?array
    {
        $sql = 'SELECT s.*,
                       p.name AS product_name,
                       p.sku,
                       p.barcode,
                       p.unit,
                       w.name AS warehouse_name,
                       w.code AS warehouse_code,
                       wl.label AS location_label
                FROM stocks s
                INNER JOIN products p ON p.id = s.product_id
                INNER JOIN warehouses w ON w.id = s.warehouse_id
                LEFT JOIN warehouse_locations wl ON wl.id = s.location_id
                WHERE s.id = :id';
        if (!$this->isSuperAdmin()) {
            $sql .= ' AND s.company_id = :company_id';
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
}
