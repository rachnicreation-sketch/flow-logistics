<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Customer extends Model
{
    protected string $table = 'customers';

    public function createCustomer(array $data): int
    {
        return $this->insert([
            'company_id' => $this->currentCompanyId(),
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function updateCustomer(int $id, array $data): bool
    {
        return $this->updateById($id, [
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function deleteCustomer(int $id): bool
    {
        return $this->deleteById($id);
    }
}
