<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

final class Payment extends Model
{
    protected string $table = 'payments';

    public function recordPayment(array $data): int
    {
        return $this->insert([
            'company_id' => $this->resolveCompanyId(),
            'invoice_id' => $data['invoice_id'] ?? null,
            'purchase_id' => $data['purchase_id'] ?? null,
            'type' => $data['type'], // 'incoming' or 'outgoing'
            'amount' => $data['amount'],
            'payment_method' => $data['payment_method'],
            'reference' => $data['reference'] ?? null,
            'payment_date' => $data['payment_date'] ?? date('Y-m-d'),
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function paymentsForInvoice(int $invoiceId): array
    {
        $sql = 'SELECT * FROM payments WHERE invoice_id = :invoice_id ORDER BY payment_date DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':invoice_id', $invoiceId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
