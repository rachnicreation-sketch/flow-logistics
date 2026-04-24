<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

final class Product extends Model
{
    protected string $table = 'products';

    public function listWithCategory(): array
    {
        $sql = 'SELECT p.*, c.name AS category_name
                FROM products p
                LEFT JOIN categories c ON c.id = p.category_id';
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

    public function createProduct(array $data): int
    {
        $barcode = trim((string) ($data['barcode'] ?? ''));
        if ($barcode === '') {
            $barcode = $this->generateBarcode();
        }

        return $this->insert([
            'company_id' => $this->currentCompanyId(),
            'category_id' => $data['category_id'] ?: null,
            'name' => $data['name'],
            'sku' => $data['sku'],
            'barcode' => $barcode,
            'unit' => $data['unit'] ?? 'piece',
            'purchase_price' => $data['purchase_price'] ?? 0,
            'sale_price' => $data['sale_price'] ?? 0,
            'min_stock' => $data['min_stock'] ?? 0,
            'status' => $data['status'] ?? 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function updateProduct(int $id, array $data): bool
    {
        $barcode = array_key_exists('barcode', $data) ? trim((string) $data['barcode']) : null;
        return $this->updateById($id, [
            'category_id' => $data['category_id'] ?: null,
            'name' => $data['name'],
            'sku' => $data['sku'],
            'barcode' => $barcode,
            'unit' => $data['unit'] ?? 'piece',
            'purchase_price' => $data['purchase_price'] ?? 0,
            'sale_price' => $data['sale_price'] ?? 0,
            'min_stock' => $data['min_stock'] ?? 0,
            'status' => $data['status'] ?? 'active',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function deleteProduct(int $id): bool
    {
        return $this->deleteById($id);
    }

    public function clearBarcode(int $id): bool
    {
        return $this->updateById($id, [
            'barcode' => null,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function detail(int $id): ?array
    {
        $sql = 'SELECT p.*,
                       c.name AS category_name,
                       COALESCE(SUM(s.quantity), 0) AS current_stock
                FROM products p
                LEFT JOIN categories c ON c.id = p.category_id
                LEFT JOIN stocks s ON s.product_id = p.id AND s.company_id = p.company_id
                WHERE p.id = :id';
        if (!$this->isSuperAdmin()) {
            $sql .= ' AND p.company_id = :company_id';
        }
        $sql .= ' GROUP BY p.id, c.name LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        if (!$this->isSuperAdmin()) {
            $stmt->bindValue(':company_id', $this->currentCompanyId(), PDO::PARAM_INT);
        }
        $stmt->execute();
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function stockByWarehouse(int $id): array
    {
        $sql = 'SELECT s.id AS stock_id,
                       s.quantity,
                       w.name AS warehouse_name,
                       wl.label AS location_label
                FROM stocks s
                INNER JOIN warehouses w ON w.id = s.warehouse_id
                LEFT JOIN warehouse_locations wl ON wl.id = s.location_id
                WHERE s.product_id = :product_id';
        if (!$this->isSuperAdmin()) {
            $sql .= ' AND s.company_id = :company_id';
        }
        $sql .= ' ORDER BY s.quantity DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':product_id', $id, PDO::PARAM_INT);
        if (!$this->isSuperAdmin()) {
            $stmt->bindValue(':company_id', $this->currentCompanyId(), PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function lowStock(): array
    {
        $sql = 'SELECT p.id, p.name, p.sku, p.min_stock, COALESCE(SUM(s.quantity), 0) AS current_stock
                FROM products p
                LEFT JOIN stocks s ON s.product_id = p.id AND s.company_id = p.company_id';
        if (!$this->isSuperAdmin()) {
            $sql .= ' WHERE p.company_id = :company_id';
        }
        $sql .= ' GROUP BY p.id, p.name, p.sku, p.min_stock HAVING current_stock <= p.min_stock
                  ORDER BY current_stock ASC';
        $stmt = $this->db->prepare($sql);
        if (!$this->isSuperAdmin()) {
            $stmt->bindValue(':company_id', $this->currentCompanyId(), PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function forecastNeeds(): array
    {
        $sql = 'SELECT p.id, p.name, p.sku, p.min_stock,
                    COALESCE(SUM(s.quantity), 0) AS current_stock,
                    COALESCE(AVG(oi.quantity), 0) AS avg_sales_line
                FROM products p
                LEFT JOIN stocks s ON s.product_id = p.id AND s.company_id = p.company_id
                LEFT JOIN order_items oi ON oi.product_id = p.id
                LEFT JOIN orders o ON o.id = oi.order_id AND o.company_id = p.company_id
                    AND o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)';
        if (!$this->isSuperAdmin()) {
            $sql .= ' WHERE p.company_id = :company_id';
        }
        $sql .= ' GROUP BY p.id, p.name, p.sku, p.min_stock
                  ORDER BY (p.min_stock - COALESCE(SUM(s.quantity), 0)) DESC';
        $stmt = $this->db->prepare($sql);
        if (!$this->isSuperAdmin()) {
            $stmt->bindValue(':company_id', $this->currentCompanyId(), PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function generateBarcode(): string
    {
        for ($i = 0; $i < 8; $i++) {
            $base = '200' . str_pad((string) random_int(0, 999999999), 9, '0', STR_PAD_LEFT);
            $checksum = $this->ean13Checksum($base);
            $candidate = $base . $checksum;

            $sql = 'SELECT COUNT(*) FROM products WHERE barcode = :barcode';
            if (!$this->isSuperAdmin()) {
                $sql .= ' AND company_id = :company_id';
            }
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':barcode', $candidate);
            if (!$this->isSuperAdmin()) {
                $stmt->bindValue(':company_id', $this->currentCompanyId(), PDO::PARAM_INT);
            }
            $stmt->execute();
            if ((int) $stmt->fetchColumn() === 0) {
                return $candidate;
            }
        }

        return '200' . date('ymdHis') . random_int(0, 9);
    }

    private function ean13Checksum(string $base12): int
    {
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $digit = (int) $base12[$i];
            $sum += ($i % 2 === 0) ? $digit : $digit * 3;
        }

        return (10 - ($sum % 10)) % 10;
    }
}
