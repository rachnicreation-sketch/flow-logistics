<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

final class CustomsDeclaration extends Model
{
    protected string $table = 'customs_declarations';

    public function listWithDetails(): array
    {
        $sql = 'SELECT cd.*,
                       o.reference AS order_reference,
                       p.reference AS purchase_reference
                FROM customs_declarations cd
                LEFT JOIN orders o ON o.id = cd.order_id
                LEFT JOIN purchases p ON p.id = cd.purchase_id';

        if (!$this->isSuperAdmin()) {
            $sql .= ' WHERE cd.company_id = :company_id';
        }
        $sql .= ' ORDER BY cd.created_at DESC';

        $stmt = $this->db->prepare($sql);
        if (!$this->isSuperAdmin()) {
            $stmt->bindValue(':company_id', $this->currentCompanyId(), PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function createDeclaration(array $data): int
    {
        return $this->insert([
            'company_id' => $this->resolveCompanyId(),
            'order_id' => $data['order_id'] ?: null,
            'purchase_id' => $data['purchase_id'] ?: null,
            'declaration_number' => $data['declaration_number'] ?? ('DCL-' . date('Ymd') . '-' . random_int(1000, 9999)),
            'type' => $data['type'], // import, export, transit
            'customs_office' => $data['customs_office'],
            'taxes_amount' => $data['taxes_amount'] ?? 0,
            'status' => 'draft',
            'cleared_at' => null,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function updateStatus(int $id, string $status): bool
    {
        $cleared = $status === 'cleared' ? date('Y-m-d H:i:s') : null;
        return $this->updateById($id, [
            'status' => $status,
            'cleared_at' => $cleared
        ]);
    }
}
