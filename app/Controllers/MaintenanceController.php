<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Flash;
use App\Models\VehicleMaintenance;
use App\Models\Vehicle;

final class MaintenanceController extends Controller
{
    public function index(): void
    {
        $this->view('maintenances/index', [
            'maintenances' => (new VehicleMaintenance())->listWithVehicles(),
            'vehicles' => (new Vehicle())->all('plate_number ASC')
        ]);
    }

    public function store(): void
    {
        $vehicleId = (int) $this->input('vehicle_id');
        $type = (string) $this->input('type');
        $description = trim((string) $this->input('description'));
        $cost = (float) $this->input('cost', 0);
        $performedAt = (string) $this->input('performed_at');
        $nextDueAt = (string) $this->input('next_due_at');

        if ($vehicleId <= 0 || $type === '' || $description === '' || $performedAt === '') {
            Flash::set('error', 'Veuillez remplir tous les champs obligatoires.');
            $this->redirect('/maintenances');
        }

        try {
            (new VehicleMaintenance())->scheduleMaintenance([
                'vehicle_id' => $vehicleId,
                'type' => $type,
                'description' => $description,
                'cost' => $cost,
                'performed_at' => $performedAt,
                'next_due_at' => $nextDueAt,
                'status' => 'planned'
            ]);
            Flash::set('success', 'Intervention programmée avec succès.');
        } catch (\Throwable $e) {
            Flash::set('error', 'Erreur lors de la programmation : ' . $e->getMessage());
        }

        $this->redirect('/maintenances');
    }

    public function updateStatus(int $id): void
    {
        $status = (string) $this->input('status');
        $validStatuses = ['planned', 'in_progress', 'completed'];

        if (!in_array($status, $validStatuses, true)) {
            Flash::set('error', 'Statut invalide.');
            $this->redirect('/maintenances');
        }

        try {
            (new VehicleMaintenance())->updateStatus($id, $status);
            Flash::set('success', 'Statut mis à jour.');
        } catch (\Throwable $e) {
            Flash::set('error', 'Erreur de mise à jour : ' . $e->getMessage());
        }

        $this->redirect('/maintenances');
    }
}
