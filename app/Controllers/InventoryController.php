<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Flash;
use App\Models\Inventory;
use App\Models\Warehouse;
use App\Services\AuditService;

final class InventoryController extends Controller
{
    public function index(): void
    {
        $this->view('inventories/index', [
            'inventories' => (new Inventory())->listWithDetails(),
            'warehouses' => (new Warehouse())->all('name ASC'),
        ]);
    }

    public function store(): void
    {
        $warehouseId = (int) $this->input('warehouse_id');
        $title = trim((string) $this->input('title'));

        if ($warehouseId <= 0 || $title === '') {
            Flash::set('error', 'Entrepôt et titre requis.');
            $this->redirect('/inventories');
        }

        try {
            $inventoryModel = new Inventory();
            $id = $inventoryModel->createInventory([
                'warehouse_id' => $warehouseId,
                'title' => $title,
            ]);
            
            // Initialiser les items basés sur le stock actuel de l'entrepôt
            $inventoryModel->addItemsFromWarehouse($id, $warehouseId);

            (new AuditService())->log('CREATE', 'inventories', $id, ['title' => $title]);
            Flash::set('success', 'Session d\'inventaire créée et initialisée.');
            $this->redirect('/inventories/show/' . $id);
        } catch (\Throwable $e) {
            Flash::set('error', 'Erreur creation inventaire : ' . $e->getMessage());
            $this->redirect('/inventories');
        }
    }

    public function show(int $id): void
    {
        $inventoryModel = new Inventory();
        $inventory = $inventoryModel->find($id);
        
        if (!$inventory) {
            Flash::set('error', 'Inventaire introuvable.');
            $this->redirect('/inventories');
        }

        $this->view('inventories/show', [
            'inventory' => $inventory,
            'warehouse' => (new Warehouse())->find((int)$inventory['warehouse_id']),
            'items' => $inventoryModel->getItems($id),
        ]);
    }

    public function updateItem(): void
    {
        $id = (int) $this->input('inventory_id');
        $itemId = (int) $this->input('item_id');
        $qty = (float) $this->input('actual_quantity');

        try {
            (new Inventory())->updateItemQuantity($itemId, $qty);
            Flash::set('success', 'Quantité mise à jour.');
        } catch (\Throwable $e) {
            Flash::set('error', 'Erreur mise à jour : ' . $e->getMessage());
        }

        $this->redirect('/inventories/show/' . $id);
    }

    public function close(int $id): void
    {
        try {
            (new Inventory())->close($id);
            (new AuditService())->log('CLOSE', 'inventories', $id);
            Flash::set('success', 'Inventaire clôturé et stocks ajustés.');
        } catch (\Throwable $e) {
            Flash::set('error', 'Erreur clôture : ' . $e->getMessage());
        }
        $this->redirect('/inventories/show/' . $id);
    }
}
