<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

final class Expense extends Model
{
    protected string $table = 'expenses';

    public function listWithDetails(): array
    {
        $sql = 'SELECT e.*, v.plate_number, u.name AS user_name
                FROM expenses e
                LEFT JOIN vehicles v ON v.id = e.vehicle_id
                LEFT JOIN users u ON u.id = e.user_id';

        if (!$this->isSuperAdmin()) {
            $sql .= ' WHERE e.company_id = :company_id';
        }
        $sql .= ' ORDER BY e.expense_date DESC';

        $stmt = $this->db->prepare($sql);
        if (!$this->isSuperAdmin()) {
            $stmt->bindValue(':company_id', $this->currentCompanyId(), PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function createExpense(array $data): int
    {
        return $this->insert([
            'company_id' => $this->resolveCompanyId(),
            'category' => $data['category'],
            'amount' => $data['amount'],
            'description' => $data['description'],
            'vehicle_id' => $data['vehicle_id'] ?: null,
            'user_id' => $data['user_id'] ?: null,
            'expense_date' => $data['expense_date'] ?? date('Y-m-d'),
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function totalByCategory(): array
    {
        $sql = 'SELECT category, SUM(amount) AS total FROM expenses';
        if (!$this->isSuperAdmin()) {
            $sql .= ' WHERE company_id = :company_id';
        }
        $sql .= ' GROUP BY category ORDER BY total DESC';
        $stmt = $this->db->prepare($sql);
        if (!$this->isSuperAdmin()) {
            $stmt->bindValue(':company_id', $this->currentCompanyId(), PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
