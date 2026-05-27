<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

final class Invoice extends Model
{
    protected string $table = 'invoices';

    public function listWithDetails(): array
    {
        $sql = 'SELECT i.*, 
                       c.name AS customer_name,
                       o.reference AS order_reference
                FROM invoices i
                INNER JOIN customers c ON c.id = i.customer_id
                LEFT JOIN orders o ON o.id = i.order_id';
        if (!$this->isSuperAdmin()) {
            $sql .= ' WHERE i.company_id = :company_id';
        }
        $sql .= ' ORDER BY i.created_at DESC';
        
        $stmt = $this->db->prepare($sql);
        if (!$this->isSuperAdmin()) {
            $stmt->bindValue(':company_id', $this->currentCompanyId(), PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function createFromOrder(array $order, array $items): int
    {
        $taxRate = 0.20; // 20% TVA par défaut
        $totalExclTax = (float) $order['total_amount'];
        $taxAmount = $totalExclTax * $taxRate;
        $totalInclTax = $totalExclTax + $taxAmount;

        return $this->insert([
            'company_id' => $this->resolveCompanyId(),
            'order_id' => $order['id'],
            'customer_id' => $order['customer_id'],
            'invoice_number' => $order['invoice_number'] ?? ('INV-' . date('Ymd') . '-' . random_int(1000, 9999)),
            'type' => 'standard',
            'total_excl_tax' => $totalExclTax,
            'tax_amount' => $taxAmount,
            'total_incl_tax' => $totalInclTax,
            'status' => 'unpaid',
            'due_date' => date('Y-m-d', strtotime('+30 days')),
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function updateStatus(int $id, string $status): bool
    {
        return $this->updateById($id, [
            'status' => $status
        ]);
    }
}
