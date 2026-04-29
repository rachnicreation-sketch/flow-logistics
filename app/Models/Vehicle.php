<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Vehicle extends Model
{
    protected string $table = 'vehicles';

    public function createVehicle(array $data): int
    {
        return $this->insert([
            'company_id' => $this->currentCompanyId(),
            'plate_number' => $data['plate_number'],
            'model' => $data['model'] ?? null,
            'capacity' => $data['capacity'] ?? null,
            'status' => $data['status'] ?? 'available',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function updateVehicle(int $id, array $data): bool
    {
        return $this->updateById($id, $data);
    }

    public function deleteVehicle(int $id): bool
    {
        return $this->deleteById($id);
    }
}

