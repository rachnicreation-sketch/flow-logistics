<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Flash;
use App\Models\Parcel;
use App\Models\Delivery;

final class ParcelController extends Controller
{
    public function index(): void
    {
        $deliveries = (new Delivery())->all('id DESC');
        
        $this->view('parcels/index', [
            'parcels' => (new Parcel())->listWithDeliveries(),
            'deliveries' => $deliveries
        ]);
    }

    public function store(): void
    {
        $deliveryId = (int) $this->input('delivery_id');
        $weight = (float) $this->input('weight_kg');
        $dimensions = trim((string) $this->input('dimensions'));

        if ($deliveryId <= 0) {
            Flash::set('error', 'Livraison obligatoire.');
            $this->redirect('/parcels');
        }

        try {
            (new Parcel())->createParcel([
                'delivery_id' => $deliveryId,
                'weight_kg' => $weight,
                'dimensions' => $dimensions,
                'barcode' => 'BC-' . date('ymd') . '-' . random_int(1000, 9999)
            ]);
            Flash::set('success', 'Colis créé et étiquette générée.');
        } catch (\Throwable $e) {
            Flash::set('error', 'Erreur lors de la création du colis : ' . $e->getMessage());
        }

        $this->redirect('/parcels');
    }

    public function updateStatus(int $id): void
    {
        $status = (string) $this->input('status');
        $validStatuses = ['prepared', 'scanned', 'loaded', 'delivered', 'lost'];
        
        if (!in_array($status, $validStatuses, true)) {
            Flash::set('error', 'Statut invalide.');
            $this->redirect('/parcels');
        }

        try {
            (new Parcel())->updateStatus($id, $status);
            Flash::set('success', 'Statut du colis mis à jour.');
        } catch (\Throwable $e) {
            Flash::set('error', 'Erreur : ' . $e->getMessage());
        }

        $this->redirect('/parcels');
    }
}
