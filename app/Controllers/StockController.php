<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Flash;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Services\AuditService;
use App\Services\NotificationService;
use App\Services\StockService;

final class StockController extends Controller
{
    public function index(): void
    {
        $this->view('stocks/index', [
            'summary' => (new Stock())->summary(),
            'movements' => (new StockMovement())->history(),
            'products' => (new Product())->all('id DESC'),
            'warehouses' => (new Warehouse())->all('id DESC'),
            'lowStock' => (new Product())->lowStock(),
            'forecast' => (new Product())->forecastNeeds(),
        ]);
    }

    public function show(int $id): void
    {
        $stock = (new Stock())->detail($id);
        if (!$stock) {
            Flash::set('error', 'Ligne de stock introuvable.');
            $this->redirect('/stocks');
        }

        $this->view('stocks/show', [
            'stock' => $stock,
            'movements' => (new StockMovement())->history(60),
        ]);
    }

    public function move(): void
    {
        try {
            (new StockService())->move([
                'product_id' => (int) $this->input('product_id'),
                'warehouse_id' => (int) $this->input('warehouse_id'),
                'location_id' => $this->input('location_id') ?: null,
                'type' => (string) $this->input('type'),
                'method' => (string) $this->input('method', 'FIFO'),
                'quantity' => (float) $this->input('quantity'),
                'unit_cost' => (float) $this->input('unit_cost', 0),
                'reference_type' => $this->input('reference_type'),
                'reference_id' => $this->input('reference_id') ?: null,
                'notes' => $this->input('notes'),
            ]);
            (new AuditService())->log('MOVE', 'stocks', null, ['type' => $this->input('type')]);

            foreach ((new Product())->lowStock() as $item) {
                (new NotificationService())->lowStockAlert(
                    $item['name'],
                    (float) $item['current_stock'],
                    (float) $item['min_stock']
                );
            }

            Flash::set('success', 'Mouvement de stock enregistre.');
        } catch (\Throwable $e) {
            Flash::set('error', 'Erreur mouvement stock: ' . $e->getMessage());
        }
        $this->redirect('/stocks');
    }
}
