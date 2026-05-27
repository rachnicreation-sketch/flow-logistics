<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

final class Supplier extends Model
{
    protected string $table = 'suppliers';

    public function createSupplier(array $data): int
    {
        return $this->insert([
            'company_id' => $this->resolveCompanyId(),
            'name' => $data['name'],
            'contact_name' => $data['contact_name'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'rating' => $data['rating'] ?? 0,
            'status' => $data['status'] ?? 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function updateSupplier(int $id, array $data): bool
    {
        return $this->updateById($id, [
            'name' => $data['name'],
            'contact_name' => $data['contact_name'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'rating' => $data['rating'] ?? 0,
            'status' => $data['status'] ?? 'active',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function history(int $supplierId): array
    {
        $sql = 'SELECT p.reference, p.status, p.total_amount, p.created_at
                FROM purchases p
                WHERE p.supplier_id = :supplier_id';
        if (!$this->isSuperAdmin()) {
            $sql .= ' AND p.company_id = :company_id';
        }
        $sql .= ' ORDER BY p.id DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':supplier_id', $supplierId, PDO::PARAM_INT);
        if (!$this->isSuperAdmin()) {
            $stmt->bindValue(':company_id', $this->currentCompanyId(), PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function deleteSupplier(int $id): bool
    {
        return $this->deleteById($id);
    }
}
