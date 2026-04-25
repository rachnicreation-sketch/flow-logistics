<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Flash;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\AuditService;
use App\Services\StockService;

final class PurchaseController extends Controller
{
    public function index(): void
    {
        $this->view('purchases/index', [
            'purchases' => (new Purchase())->listPurchases(),
            'suppliers' => (new Supplier())->all('id DESC'),
            'products' => (new Product())->all('id DESC'),
            'warehouses' => (new Warehouse())->all('id DESC'),
        ]);
    }

    public function store(): void
    {
        $supplierId = (int) $this->input('supplier_id');
        $reference = trim((string) $this->input('reference'));
        if ($supplierId <= 0 || $reference === '') {
            Flash::set('error', 'Fournisseur et référence sont requis.');
            $this->redirect('/purchases');
        }

        $items = $this->extractItems();
        if (empty($items)) {
            Flash::set('error', 'Au moins une ligne produit est nécessaire.');
            $this->redirect('/purchases');
        }

        try {
            $total = array_sum(array_map(static fn (array $i): float => $i['quantity'] * $i['unit_price'], $items));
            $id = (new Purchase())->createPurchase([
                'supplier_id' => $supplierId,
                'reference' => $reference,
                'status' => 'ordered',
                'expected_date' => $this->input('expected_date'),
                'total_amount' => $total,
            ], $items);

            (new AuditService())->log('CREATE', 'purchases', $id, ['reference' => $reference]);
            Flash::set('success', 'Bon de commande fournisseur créé.');
        } catch (\Throwable $e) {
            Flash::set('error', 'Impossible de créer l\'achat: ' . $e->getMessage());
        }

        $this->redirect('/purchases');
    }

    public function receive(int $id): void
    {
        $warehouseId = (int) $this->input('warehouse_id');
        if ($warehouseId <= 0) {
            Flash::set('error', 'Entrepôt requis pour la réception.');
            $this->redirect('/purchases');
        }

        try {
            (new StockService())->receivePurchase($id, $warehouseId, null);
            (new AuditService())->log('RECEIVE', 'purchases', $id, ['warehouse_id' => $warehouseId]);
            Flash::set('success', 'Achat réceptionné et stock mis à jour.');
        } catch (\Throwable $e) {
            Flash::set('error', 'Erreur réception: ' . $e->getMessage());
        }
        $this->redirect('/purchases');
    }

    private function extractItems(): array
    {
        $productIds = $_POST['product_id'] ?? [];
        $quantities = $_POST['quantity'] ?? [];
        $prices = $_POST['unit_price'] ?? [];
        $items = [];
        foreach ($productIds as $i => $pid) {
            $pid = (int) $pid;
            $qty = (float) ($quantities[$i] ?? 0);
            $price = (float) ($prices[$i] ?? 0);
            if ($pid > 0 && $qty > 0) {
                $items[] = [
                    'product_id' => $pid,
                    'quantity' => $qty,
                    'unit_price' => $price,
                ];
            }
        }
        return $items;
    }
}
