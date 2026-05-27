<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Flash;
use App\Models\Vehicle;
use App\Services\AuditService;

final class VehicleController extends Controller
{
    public function index(): void
    {
        $model = new Vehicle();
        $drivers = (new \App\Models\User())->listUsers();
        $this->view('vehicles/index', [
            'vehicles' => $model->all('plate_number ASC'),
            'drivers' => $drivers,
        ]);
    }

    public function create(): void
    {
        $this->view('vehicles/create');
    }

    public function store(): void
    {
        $plateNumber = trim((string) $this->input('plate_number'));
        if ($plateNumber === '') {
            Flash::set('error', 'Le numéro de plaque est obligatoire.');
            $this->redirect('/vehicles/create');
        }

        try {
            $id = (new Vehicle())->createVehicle([
                'plate_number' => $plateNumber,
                'model' => $this->input('model'),
                'capacity' => $this->input('capacity') ? (float) $this->input('capacity') : null,
                'driver_id' => $this->input('driver_id') ? (int) $this->input('driver_id') : null,
                'status' => $this->input('status') ?: 'available',
            ]);
            (new AuditService())->log('CREATE', 'vehicles', $id, ['plate_number' => $plateNumber]);
            Flash::set('success', 'Véhicule créé avec succès.');
            $this->redirect('/vehicles');
        } catch (\Throwable $e) {
            Flash::set('error', 'Impossible de créer le véhicule : ' . $e->getMessage());
            $this->redirect('/vehicles/create');
        }
    }

    public function edit(int $id): void
    {
        $vehicle = (new Vehicle())->find($id);
        if (!$vehicle) {
            Flash::set('error', 'Véhicule introuvable.');
            $this->redirect('/vehicles');
        }

        $drivers = (new \App\Models\User())->listUsers();
        $this->view('vehicles/edit', [
            'vehicle' => $vehicle,
            'drivers' => $drivers,
        ]);
    }

    public function update(int $id): void
    {
        $plateNumber = trim((string) $this->input('plate_number'));
        if ($plateNumber === '') {
            Flash::set('error', 'Le numéro de plaque est obligatoire.');
            $this->redirect("/vehicles/edit/{$id}");
        }

        try {
            (new Vehicle())->updateVehicle($id, [
                'plate_number' => $plateNumber,
                'model' => $this->input('model'),
                'capacity' => $this->input('capacity') ? (float) $this->input('capacity') : null,
                'driver_id' => $this->input('driver_id') ? (int) $this->input('driver_id') : null,
                'status' => $this->input('status') ?: 'available',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            (new AuditService())->log('UPDATE', 'vehicles', $id, ['plate_number' => $plateNumber]);
            Flash::set('success', 'Véhicule mis à jour.');
            $this->redirect('/vehicles');
        } catch (\Throwable $e) {
            Flash::set('error', 'Impossible de mettre à jour le véhicule : ' . $e->getMessage());
            $this->redirect("/vehicles/edit/{$id}");
        }
    }

    public function delete(int $id): void
    {
        try {
            (new Vehicle())->deleteVehicle($id);
            (new AuditService())->log('DELETE', 'vehicles', $id);
            Flash::set('success', 'Véhicule supprimé.');
        } catch (\Throwable $e) {
            Flash::set('error', 'Impossible de supprimer le véhicule : ' . $e->getMessage());
        }
        $this->redirect('/vehicles');
    }
}
