<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

final class Parcel extends Model
{
    protected string $table = 'parcels';

    public function listWithDeliveries(): array
    {
        $sql = 'SELECT p.*, d.status AS delivery_status, v.plate_number, o.reference AS order_reference
                FROM parcels p
                INNER JOIN deliveries d ON d.id = p.delivery_id
                INNER JOIN orders o ON o.id = d.order_id
                LEFT JOIN vehicles v ON v.id = d.vehicle_id';
        if (!$this->isSuperAdmin()) {
            $sql .= ' WHERE p.company_id = :company_id';
        }
        $sql .= ' ORDER BY p.created_at DESC';

        $stmt = $this->db->prepare($sql);
        if (!$this->isSuperAdmin()) {
            $stmt->bindValue(':company_id', $this->currentCompanyId(), PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function createParcel(array $data): int
    {
        return $this->insert([
            'company_id' => $this->resolveCompanyId(),
            'delivery_id' => $data['delivery_id'],
            'tracking_number' => $data['tracking_number'] ?? ('TRK-' . date('Ymd') . '-' . random_int(1000, 9999)),
            'weight_kg' => $data['weight_kg'] ?? 0,
            'volume_m3' => $data['volume_m3'] ?? 0,
            'dimensions' => $data['dimensions'] ?? null,
            'barcode' => $data['barcode'] ?? null,
            'status' => 'prepared',
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function updateStatus(int $id, string $status): bool
    {
        return $this->updateById($id, [
            'status' => $status
        ]);
    }

    public function parcelsByDelivery(int $deliveryId): array
    {
        $sql = 'SELECT * FROM parcels WHERE delivery_id = :delivery_id';
        if (!$this->isSuperAdmin()) {
            $sql .= ' AND company_id = :company_id';
        }
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':delivery_id', $deliveryId, PDO::PARAM_INT);
        if (!$this->isSuperAdmin()) {
            $stmt->bindValue(':company_id', $this->currentCompanyId(), PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
