<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Auth;
use App\Core\Model;
use PDO;

final class Delivery extends Model
{
    protected string $table = 'deliveries';

    public function createDelivery(array $data): int
    {
        return $this->insert([
            'company_id' => $this->resolveCompanyId(),
            'order_id' => $data['order_id'],
            'vehicle_id' => $data['vehicle_id'] ?: null,
            'driver_id' => $data['driver_id'] ?: null,
            'status' => $data['status'] ?? 'pending',
            'planned_date' => $data['planned_date'] ?? null,
            'notes' => $data['notes'] ?? null,
            'created_by' => Auth::id(),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function hasOpenDeliveryForOrder(int $orderId): bool
    {
        $sql = 'SELECT COUNT(*)
                FROM deliveries
                WHERE order_id = :order_id
                  AND status IN ("pending","in_transit")';
        if (!$this->isSuperAdmin()) {
            $sql .= ' AND company_id = :company_id';
        }
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':order_id', $orderId, PDO::PARAM_INT);
        if (!$this->isSuperAdmin()) {
            $stmt->bindValue(':company_id', $this->currentCompanyId(), PDO::PARAM_INT);
        }
        $stmt->execute();
        return (int) $stmt->fetchColumn() > 0;
    }

    public function listDeliveries(): array
    {
        $sql = 'SELECT d.*, o.reference AS order_ref, c.name AS customer_name,
                       v.plate_number, u.name AS driver_name
                FROM deliveries d
                INNER JOIN orders o ON o.id = d.order_id
                INNER JOIN customers c ON c.id = o.customer_id
                LEFT JOIN vehicles v ON v.id = d.vehicle_id
                LEFT JOIN users u ON u.id = d.driver_id';
        if (!$this->isSuperAdmin()) {
            $sql .= ' WHERE d.company_id = :company_id';
        }
        $sql .= ' ORDER BY d.id DESC';
        $stmt = $this->db->prepare($sql);
        if (!$this->isSuperAdmin()) {
            $stmt->bindValue(':company_id', $this->currentCompanyId(), PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function forDriver(int $driverId): array
    {
        $stmt = $this->db->prepare(
            'SELECT d.*, o.reference AS order_ref, o.delivery_address, c.name AS customer_name
             FROM deliveries d
             INNER JOIN orders o ON o.id = d.order_id
             INNER JOIN customers c ON c.id = o.customer_id
             WHERE d.driver_id = :driver_id AND d.company_id = :company_id
             ORDER BY d.id DESC'
        );
        $stmt->execute([
            'driver_id' => $driverId,
            'company_id' => $this->resolveCompanyId(),
        ]);
        return $stmt->fetchAll();
    }

    public function updateStatus(
        int $deliveryId,
        string $status,
        ?float $lat = null,
        ?float $lng = null,
        ?string $notes = null
    ): bool {
        $data = [
            'status' => $status,
            'last_lat' => $lat,
            'last_lng' => $lng,
            'driver_notes' => $notes,
            'updated_by' => Auth::id(),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if ($status === 'delivered') {
            $data['delivered_at'] = date('Y-m-d H:i:s');
        }
        return $this->updateById($deliveryId, $data);
    }

    public function updateStatusByDriver(
        int $deliveryId,
        int $driverId,
        string $status,
        ?float $lat = null,
        ?float $lng = null,
        ?string $notes = null
    ): bool {
        $sql = 'UPDATE deliveries
                SET status = :status,
                    last_lat = :last_lat,
                    last_lng = :last_lng,
                    driver_notes = :driver_notes,
                    updated_by = :updated_by,
                    updated_at = :updated_at' .
                ($status === 'delivered' ? ', delivered_at = :delivered_at' : '') . '
                WHERE id = :id
                  AND driver_id = :driver_id
                  AND company_id = :company_id';

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':status', $status);
        $stmt->bindValue(':last_lat', $lat);
        $stmt->bindValue(':last_lng', $lng);
        $stmt->bindValue(':driver_notes', $notes);
        $stmt->bindValue(':updated_by', $driverId, PDO::PARAM_INT);
        $stmt->bindValue(':updated_at', date('Y-m-d H:i:s'));
        $stmt->bindValue(':id', $deliveryId, PDO::PARAM_INT);
        $stmt->bindValue(':driver_id', $driverId, PDO::PARAM_INT);
        $stmt->bindValue(':company_id', $this->currentCompanyId(), PDO::PARAM_INT);
        if ($status === 'delivered') {
            $stmt->bindValue(':delivered_at', date('Y-m-d H:i:s'));
        }
        return $stmt->execute();
    }

    public function inProgressCount(): int
    {
        $sql = 'SELECT COUNT(*) FROM deliveries WHERE status IN ("pending","in_transit")';
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
