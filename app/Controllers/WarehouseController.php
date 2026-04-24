<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Flash;
use App\Models\Warehouse;
use App\Services\AuditService;

final class WarehouseController extends Controller
{
    public function index(): void
    {
        $model = new Warehouse();
        $warehouses = $model->all('id DESC');
        $zones = [];
        foreach ($warehouses as $warehouse) {
            $zones[(int) $warehouse['id']] = $model->zonesByWarehouse((int) $warehouse['id']);
        }

        $this->view('warehouses/index', [
            'warehouses' => $warehouses,
            'zonesByWarehouse' => $zones,
            'locations' => $model->locationsWithContext(),
        ]);
    }

    public function show(int $id): void
    {
        $model = new Warehouse();
        $warehouse = $model->detail($id);
        if (!$warehouse) {
            Flash::set('error', 'Entrepot introuvable.');
            $this->redirect('/warehouses');
        }

        $this->view('warehouses/show', [
            'warehouse' => $warehouse,
            'zones' => $model->zonesWithLocations($id),
        ]);
    }

    public function locationShow(int $id): void
    {
        $location = (new Warehouse())->locationDetail($id);
        if (!$location) {
            Flash::set('error', 'Emplacement introuvable.');
            $this->redirect('/warehouses');
        }

        $this->view('warehouses/location', [
            'location' => $location,
        ]);
    }

    public function store(): void
    {
        $name = trim((string) $this->input('name'));
        $code = strtoupper(trim((string) $this->input('code')));
        if ($name === '' || $code === '') {
            Flash::set('error', 'Nom et code entrepot obligatoires.');
            $this->redirect('/warehouses');
        }

        try {
            $id = (new Warehouse())->createWarehouse([
                'name' => $name,
                'code' => $code,
                'address' => $this->input('address'),
            ]);
            (new AuditService())->log('CREATE', 'warehouses', $id, ['code' => $code]);
            Flash::set('success', 'Entrepot cree.');
        } catch (\Throwable $e) {
            Flash::set('error', 'Impossible de creer l\'entrepot: ' . $e->getMessage());
        }

        $this->redirect('/warehouses');
    }

    public function storeZone(): void
    {
        $warehouseId = (int) $this->input('warehouse_id');
        $name = trim((string) $this->input('name'));
        if ($warehouseId <= 0 || $name === '') {
            Flash::set('error', 'Zone invalide.');
            $this->redirect('/warehouses');
        }

        try {
            $id = (new Warehouse())->createZone([
                'warehouse_id' => $warehouseId,
                'name' => $name,
            ]);
            (new AuditService())->log('CREATE', 'warehouse_zones', $id, ['warehouse_id' => $warehouseId]);
            Flash::set('success', 'Zone creee.');
        } catch (\Throwable $e) {
            Flash::set('error', 'Impossible de creer la zone: ' . $e->getMessage());
        }

        $this->redirect('/warehouses');
    }

    public function storeLocation(): void
    {
        $zoneId = (int) $this->input('zone_id');
        $label = trim((string) $this->input('label'));
        if ($zoneId <= 0 || $label === '') {
            Flash::set('error', 'Emplacement invalide.');
            $this->redirect('/warehouses');
        }

        try {
            $id = (new Warehouse())->createLocation([
                'zone_id' => $zoneId,
                'label' => $label,
                'capacity' => $this->input('capacity'),
            ]);
            (new AuditService())->log('CREATE', 'warehouse_locations', $id, ['zone_id' => $zoneId]);
            Flash::set('success', 'Emplacement ajoute.');
        } catch (\Throwable $e) {
            Flash::set('error', 'Impossible d\'ajouter l\'emplacement: ' . $e->getMessage());
        }

        $this->redirect('/warehouses');
    }
}
