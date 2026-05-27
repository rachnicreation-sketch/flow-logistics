<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Notification;
use App\Services\DashboardService;

final class DashboardController extends Controller
{
    public function index(): void
    {
        $service = new DashboardService();
        $this->view('dashboard/index', [
            'metrics'             => $service->metrics(),
            'salesByMonth'        => $service->salesByMonth(),
            'stockByWarehouse'    => $service->stockByWarehouse(),
            'revenueVsExpenses'   => $service->revenueVsExpenses(),
            'notifications'       => (new Notification())->forCurrentUser(),
            'lowStockProducts'    => (new \App\Models\Product())->lowStock(),
        ]);
    }
}

