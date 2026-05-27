<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

final class Warehouse extends Model
{
    protected string $table = 'warehouses';

    public function createWarehouse(array $data): int
    {
        return $this->insert([
            'company_id' => $this->resolveCompanyId(),
            'name' => $data['name'],
            'code' => $data['code'],
            'address' => $data['address'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function updateWarehouse(int $id, array $data): bool
    {
        return $this->updateById($id, $data);
    }

    public function deleteWarehouse(int $id): bool
    {
        return $this->deleteById($id);
    }

    public function createZone(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO warehouse_zones (company_id, warehouse_id, name, created_at)
             VALUES (:company_id, :warehouse_id, :name, :created_at)'
        );
        $stmt->execute([
            'company_id' => $this->resolveCompanyId(),
            'warehouse_id' => $data['warehouse_id'],
            'name' => $data['name'],
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function createLocation(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO warehouse_locations (company_id, zone_id, label, capacity, created_at)
             VALUES (:company_id, :zone_id, :label, :capacity, :created_at)'
        );
        $stmt->execute([
            'company_id' => $this->resolveCompanyId(),
            'zone_id' => $data['zone_id'],
            'label' => $data['label'],
            'capacity' => $data['capacity'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function deleteLocation(int $id): bool
    {
        $sql = 'DELETE FROM warehouse_locations WHERE id = :id';
        if (!$this->isSuperAdmin()) {
            $sql .= ' AND company_id = :company_id';
        }
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        if (!$this->isSuperAdmin()) {
            $stmt->bindValue(':company_id', $this->currentCompanyId(), PDO::PARAM_INT);
        }
        return $stmt->execute();
    }

    public function zonesByWarehouse(int $warehouseId): array
    {
        $sql = 'SELECT * FROM warehouse_zones WHERE warehouse_id = :warehouse_id';
        if (!$this->isSuperAdmin()) {
            $sql .= ' AND company_id = :company_id';
        }
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':warehouse_id', $warehouseId, PDO::PARAM_INT);
        if (!$this->isSuperAdmin()) {
            $stmt->bindValue(':company_id', $this->currentCompanyId(), PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function locationsByZone(int $zoneId): array
    {
        $sql = 'SELECT * FROM warehouse_locations WHERE zone_id = :zone_id';
        if (!$this->isSuperAdmin()) {
            $sql .= ' AND company_id = :company_id';
        }
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':zone_id', $zoneId, PDO::PARAM_INT);
        if (!$this->isSuperAdmin()) {
            $stmt->bindValue(':company_id', $this->currentCompanyId(), PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function locationsWithContext(): array
    {
        $sql = 'SELECT wl.*, wz.name AS zone_name, w.name AS warehouse_name
                FROM warehouse_locations wl
                INNER JOIN warehouse_zones wz ON wz.id = wl.zone_id
                INNER JOIN warehouses w ON w.id = wz.warehouse_id';
        if (!$this->isSuperAdmin()) {
            $sql .= ' WHERE wl.company_id = :company_id';
        }
        $sql .= ' ORDER BY wl.id DESC';
        $stmt = $this->db->prepare($sql);
        if (!$this->isSuperAdmin()) {
            $stmt->bindValue(':company_id', $this->currentCompanyId(), PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function detail(int $warehouseId): ?array
    {
        $sql = 'SELECT w.*,
                       COUNT(DISTINCT wz.id) AS zone_count,
                       COUNT(DISTINCT wl.id) AS location_count,
                       COALESCE(SUM(s.quantity), 0) AS total_stock
                FROM warehouses w
                LEFT JOIN warehouse_zones wz ON wz.warehouse_id = w.id
                LEFT JOIN warehouse_locations wl ON wl.zone_id = wz.id
                LEFT JOIN stocks s ON s.warehouse_id = w.id AND s.company_id = w.company_id
                WHERE w.id = :id';
        if (!$this->isSuperAdmin()) {
            $sql .= ' AND w.company_id = :company_id';
        }
        $sql .= ' GROUP BY w.id LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $warehouseId, PDO::PARAM_INT);
        if (!$this->isSuperAdmin()) {
            $stmt->bindValue(':company_id', $this->currentCompanyId(), PDO::PARAM_INT);
        }
        $stmt->execute();
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function zonesWithLocations(int $warehouseId): array
    {
        $sql = 'SELECT wz.id AS zone_id, wz.name AS zone_name,
                       wl.id AS location_id, wl.label, wl.capacity
                FROM warehouse_zones wz
                LEFT JOIN warehouse_locations wl ON wl.zone_id = wz.id
                WHERE wz.warehouse_id = :warehouse_id';
        if (!$this->isSuperAdmin()) {
            $sql .= ' AND wz.company_id = :company_id';
        }
        $sql .= ' ORDER BY wz.name ASC, wl.label ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':warehouse_id', $warehouseId, PDO::PARAM_INT);
        if (!$this->isSuperAdmin()) {
            $stmt->bindValue(':company_id', $this->currentCompanyId(), PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function locationDetail(int $locationId): ?array
    {
        $sql = 'SELECT wl.*,
                       wz.name AS zone_name,
                       w.id AS warehouse_id,
                       w.name AS warehouse_name,
                       w.code AS warehouse_code,
                       COALESCE(SUM(s.quantity), 0) AS stock_quantity
                FROM warehouse_locations wl
                INNER JOIN warehouse_zones wz ON wz.id = wl.zone_id
                INNER JOIN warehouses w ON w.id = wz.warehouse_id
                LEFT JOIN stocks s ON s.location_id = wl.id AND s.company_id = wl.company_id
                WHERE wl.id = :id';
        if (!$this->isSuperAdmin()) {
            $sql .= ' AND wl.company_id = :company_id';
        }
        $sql .= ' GROUP BY wl.id, wz.name, w.id, w.name, w.code LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $locationId, PDO::PARAM_INT);
        if (!$this->isSuperAdmin()) {
            $stmt->bindValue(':company_id', $this->currentCompanyId(), PDO::PARAM_INT);
        }
        $stmt->execute();
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
