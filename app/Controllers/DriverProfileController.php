<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Flash;
use App\Models\DriverProfile;

final class DriverProfileController extends Controller
{
    public function index(): void
    {
        $this->view('drivers/index', [
            'drivers' => (new DriverProfile())->listWithUsers()
        ]);
    }

    public function store(): void
    {
        $userId = (int) $this->input('user_id');
        $licenseNumber = trim((string) $this->input('license_number'));
        $licenseType = trim((string) $this->input('license_type'));
        $licenseExpiry = trim((string) $this->input('license_expiry'));
        $totalHours = (float) $this->input('total_hours', 0);
        $bonuses = (float) $this->input('bonuses', 0);
        $penalties = (float) $this->input('penalties', 0);
        $status = (string) $this->input('status', 'available');

        if ($userId <= 0 || $licenseNumber === '' || $licenseType === '' || $licenseExpiry === '') {
            Flash::set('error', 'Les informations du permis sont obligatoires.');
            $this->redirect('/drivers');
        }

        try {
            (new DriverProfile())->upsertProfile($userId, [
                'license_number' => $licenseNumber,
                'license_type' => $licenseType,
                'license_expiry' => $licenseExpiry,
                'total_hours' => $totalHours,
                'bonuses' => $bonuses,
                'penalties' => $penalties,
                'status' => $status
            ]);
            Flash::set('success', 'Profil chauffeur mis à jour avec succès.');
        } catch (\Throwable $e) {
            Flash::set('error', 'Erreur de mise à jour : ' . $e->getMessage());
        }

        $this->redirect('/drivers');
    }

    public function updateStatus(int $userId): void
    {
        $status = (string) $this->input('status');
        $validStatuses = ['available', 'on_leave', 'sick', 'suspended'];

        if (!in_array($status, $validStatuses, true)) {
            Flash::set('error', 'Statut invalide.');
            $this->redirect('/drivers');
        }

        try {
            (new DriverProfile())->updateStatus($userId, $status);
            Flash::set('success', 'Statut du chauffeur mis à jour.');
        } catch (\Throwable $e) {
            Flash::set('error', 'Erreur : ' . $e->getMessage());
        }

        $this->redirect('/drivers');
    }
}
