<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Auth;
use App\Core\Database;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\Product;
use App\Models\Stock;
use PDO;

final class DashboardService
{
    private function companyId(): ?int
    {
        return (Auth::user()['role_slug'] ?? '') !== 'super_admin'
            ? Auth::companyId()
            : null;
    }

    private function bindCompany(\PDOStatement $stmt): void
    {
        $cid = $this->companyId();
        if ($cid !== null) {
            $stmt->bindValue(':company_id', $cid, PDO::PARAM_INT);
        }
    }

    private function scalar(string $sql): mixed
    {
        $db = Database::connection();
        $stmt = $db->prepare($sql);
        $this->bindCompany($stmt);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function metrics(): array
    {
        $cid = $this->companyId();
        $w = $cid ? ' AND company_id = :company_id' : '';

        return [
            'stock_total'            => (new Stock())->totalQuantity(),
            'ruptures'               => count((new Product())->lowStock()),
            'orders_in_progress'     => (new Order())->pendingCount(),
            'deliveries_in_progress' => (new Delivery())->inProgressCount(),
            'total_revenue'          => (float) $this->scalar('SELECT COALESCE(SUM(total_incl_tax),0) FROM invoices WHERE status="paid"' . $w),
            'unpaid_invoices'        => (float) $this->scalar('SELECT COALESCE(SUM(total_incl_tax),0) FROM invoices WHERE status IN ("unpaid","partially_paid")' . $w),
            'recent_expenses'        => (float) $this->scalar('SELECT COALESCE(SUM(amount),0) FROM expenses WHERE expense_date >= DATE_SUB(CURDATE(),INTERVAL 30 DAY)' . $w),
            'pending_returns'        => (int)   $this->scalar('SELECT COUNT(*) FROM return_authorizations WHERE status IN ("requested","approved")' . $w),
            'planned_maintenances'   => (int)   $this->scalar('SELECT COUNT(*) FROM vehicle_maintenances WHERE status="planned"' . $w),
        ];
    }

    public function salesByMonth(): array
    {
        $db = Database::connection();
        $sql = 'SELECT DATE_FORMAT(created_at, "%Y-%m") AS month_key,
                       COALESCE(SUM(total_amount), 0) AS total_sales
                FROM orders
                WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)';
        if ($this->companyId()) {
            $sql .= ' AND company_id = :company_id';
        }
        $sql .= ' GROUP BY DATE_FORMAT(created_at, "%Y-%m") ORDER BY month_key ASC';
        $stmt = $db->prepare($sql);
        $this->bindCompany($stmt);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function stockByWarehouse(): array
    {
        $db = Database::connection();
        $sql = 'SELECT w.name AS warehouse_name, COALESCE(SUM(s.quantity),0) AS total_qty
                FROM warehouses w
                LEFT JOIN stocks s ON s.warehouse_id = w.id AND s.company_id = w.company_id';
        if ($this->companyId()) {
            $sql .= ' WHERE w.company_id = :company_id';
        }
        $sql .= ' GROUP BY w.id, w.name ORDER BY total_qty DESC';
        $stmt = $db->prepare($sql);
        $this->bindCompany($stmt);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function revenueVsExpenses(): array
    {
        $db  = Database::connection();
        $cid = $this->companyId();

        $revSQL = 'SELECT DATE_FORMAT(created_at, "%Y-%m") AS month_key,
                          COALESCE(SUM(total_incl_tax),0) AS val
                   FROM invoices WHERE status="paid"'
            . ($cid ? ' AND company_id = :company_id' : '')
            . ' AND created_at >= DATE_SUB(CURDATE(),INTERVAL 6 MONTH)
                GROUP BY month_key ORDER BY month_key ASC';
        $stmt = $db->prepare($revSQL);
        $this->bindCompany($stmt);
        $stmt->execute();
        $revRows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        $expSQL = 'SELECT DATE_FORMAT(expense_date, "%Y-%m") AS month_key,
                          COALESCE(SUM(amount),0) AS val
                   FROM expenses'
            . ($cid ? ' WHERE company_id = :company_id AND' : ' WHERE')
            . ' expense_date >= DATE_SUB(CURDATE(),INTERVAL 6 MONTH)
                GROUP BY month_key ORDER BY month_key ASC';
        $stmt2 = $db->prepare($expSQL);
        $this->bindCompany($stmt2);
        $stmt2->execute();
        $expRows = $stmt2->fetchAll(PDO::FETCH_KEY_PAIR);

        $allMonths = array_unique(array_merge(array_keys($revRows), array_keys($expRows)));
        sort($allMonths);

        $result = [];
        foreach ($allMonths as $m) {
            $result[] = [
                'month_key' => $m,
                'revenue'   => (float) ($revRows[$m] ?? 0),
                'expenses'  => (float) ($expRows[$m] ?? 0),
            ];
        }
        return $result;
    }
}
