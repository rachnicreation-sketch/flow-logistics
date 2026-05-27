<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

final class VehicleMaintenance extends Model
{
    protected string $table = 'vehicle_maintenances';

    public function listWithVehicles(): array
    {
        $sql = 'SELECT m.*, v.plate_number, v.model 
                FROM vehicle_maintenances m
                INNER JOIN vehicles v ON v.id = m.vehicle_id';
        
        if (!$this->isSuperAdmin()) {
            $sql .= ' WHERE m.company_id = :company_id';
        }
        $sql .= ' ORDER BY m.performed_at DESC';

        $stmt = $this->db->prepare($sql);
        if (!$this->isSuperAdmin()) {
            $stmt->bindValue(':company_id', $this->currentCompanyId(), PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function scheduleMaintenance(array $data): int
    {
        return $this->insert([
            'company_id' => $this->resolveCompanyId(),
            'vehicle_id' => $data['vehicle_id'],
            'type' => $data['type'], // 'routine', 'repair', 'inspection', 'insurance'
            'description' => $data['description'],
            'cost' => $data['cost'] ?? 0,
            'performed_at' => $data['performed_at'],
            'next_due_at' => $data['next_due_at'] ?: null,
            'status' => $data['status'] ?? 'planned',
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function updateStatus(int $id, string $status): bool
    {
        return $this->updateById($id, ['status' => $status]);
    }

    public function calculateTotalCostForVehicle(int $vehicleId): float
    {
        $sql = 'SELECT SUM(cost) as total FROM vehicle_maintenances WHERE vehicle_id = :vid AND status = "completed"';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':vid', $vehicleId, PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetch();
        return (float) ($res['total'] ?? 0);
    }
}
