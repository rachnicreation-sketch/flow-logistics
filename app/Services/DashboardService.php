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
    public function metrics(): array
    {
        $stock = new Stock();
        $product = new Product();
        $order = new Order();
        $delivery = new Delivery();

        return [
            'stock_total' => $stock->totalQuantity(),
            'ruptures' => count($product->lowStock()),
            'orders_in_progress' => $order->pendingCount(),
            'deliveries_in_progress' => $delivery->inProgressCount(),
        ];
    }

    public function salesByMonth(): array
    {
        $db = Database::connection();
        $sql = 'SELECT DATE_FORMAT(created_at, "%Y-%m") AS month_key,
                       COALESCE(SUM(total_amount), 0) AS total_sales
                FROM orders
                WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)';
        if ((Auth::user()['role_slug'] ?? '') !== 'super_admin') {
            $sql .= ' AND company_id = :company_id';
        }
        $sql .= ' GROUP BY DATE_FORMAT(created_at, "%Y-%m")
                  ORDER BY month_key ASC';
        $stmt = $db->prepare($sql);
        if ((Auth::user()['role_slug'] ?? '') !== 'super_admin') {
            $stmt->bindValue(':company_id', Auth::companyId(), PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function stockByWarehouse(): array
    {
        $db = Database::connection();
        $sql = 'SELECT w.name AS warehouse_name, COALESCE(SUM(s.quantity),0) AS total_qty
                FROM warehouses w
                LEFT JOIN stocks s ON s.warehouse_id = w.id AND s.company_id = w.company_id';
        if ((Auth::user()['role_slug'] ?? '') !== 'super_admin') {
            $sql .= ' WHERE w.company_id = :company_id';
        }
        $sql .= ' GROUP BY w.id, w.name ORDER BY total_qty DESC';
        $stmt = $db->prepare($sql);
        if ((Auth::user()['role_slug'] ?? '') !== 'super_admin') {
            $stmt->bindValue(':company_id', Auth::companyId(), PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }
}

