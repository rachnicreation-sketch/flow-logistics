<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Flash;
use App\Models\Delivery;
use App\Models\Notification;
use App\Models\Order;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\AuditService;

final class DeliveryController extends Controller
{
    public function index(): void
    {
        $orderModel = new Order();
        $allOrders = $orderModel->listOrders();
        $eligibleOrders = array_values(array_filter(
            $allOrders,
            static fn (array $o): bool => !in_array((string) ($o['status'] ?? ''), ['delivered', 'cancelled'], true)
        ));

        $users = array_values(array_filter((new User())->listUsers(), static fn (array $u): bool => $u['role_slug'] === 'driver'));
        $this->view('deliveries/index', [
            'deliveries' => (new Delivery())->listDeliveries(),
            'orders' => $eligibleOrders,
            'vehicles' => (new Vehicle())->all('id DESC'),
            'drivers' => $users,
            'selectedOrderId' => (int) $this->input('order_id', 0),
        ]);
    }

    public function storeVehicle(): void
    {
        $plate = strtoupper(trim((string) $this->input('plate_number')));
        if ($plate === '') {
            Flash::set('error', 'Matricule véhicule obligatoire.');
            $this->redirect('/deliveries');
        }

        try {
            $id = (new Vehicle())->createVehicle([
                'plate_number' => $plate,
                'model' => $this->input('model'),
                'capacity' => $this->input('capacity'),
                'status' => $this->input('status', 'available'),
            ]);
            (new AuditService())->log('CREATE', 'vehicles', $id, ['plate' => $plate]);
            Flash::set('success', 'Véhicule créé.');
        } catch (\Throwable $e) {
            Flash::set('error', 'Impossible de créer le véhicule: ' . $e->getMessage());
        }

        $this->redirect('/deliveries');
    }

    public function store(): void
    {
        $orderId = (int) $this->input('order_id');
        if ($orderId <= 0) {
            Flash::set('error', 'Commande requise.');
            $this->redirect('/deliveries');
        }

        $deliveryModel = new Delivery();
        if ($deliveryModel->hasOpenDeliveryForOrder($orderId)) {
            Flash::set('error', 'Cette commande a deja une livraison en cours.');
            $this->redirect('/deliveries');
        }

        try {
            $driverId = (int) $this->input('driver_id');
            $id = $deliveryModel->createDelivery([
                'order_id' => $orderId,
                'vehicle_id' => (int) $this->input('vehicle_id'),
                'driver_id' => $driverId,
                'status' => 'pending',
                'planned_date' => $this->normalizeDateTime((string) $this->input('planned_date', '')),
                'notes' => $this->input('notes'),
            ]);

            if ($driverId > 0) {
                (new Notification())->createForUser(
                    $driverId,
                    'delivery',
                    'Nouvelle livraison assignée',
                    'Une livraison vous a ete assignée. Consultez votre espace chauffeur.'
                );
            }

            (new AuditService())->log('CREATE', 'deliveries', $id, ['order_id' => $orderId]);
            Flash::set('success', 'Livraison planifiée.');
        } catch (\Throwable $e) {
            Flash::set('error', 'Erreur de planification: ' . $e->getMessage());
        }

        $this->redirect('/deliveries');
    }

    public function updateStatus(int $id): void
    {
        $status = (string) $this->input('status');

        try {
            (new Delivery())->updateStatus(
                $id,
                $status,
                $this->input('lat') !== null ? (float) $this->input('lat') : null,
                $this->input('lng') !== null ? (float) $this->input('lng') : null,
                (string) $this->input('driver_notes', '')
            );
            $this->syncOrderStatusFromDelivery($id, $status);
            (new AuditService())->log('UPDATE_STATUS', 'deliveries', $id, ['status' => $status]);
            Flash::set('success', 'Statut livraison mis à jour.');
        } catch (\Throwable $e) {
            Flash::set('error', 'Erreur mise à jour statut: ' . $e->getMessage());
        }

        $this->redirect('/deliveries');
    }

    public function driverPanel(): void
    {
        $user = Auth::user();
        $deliveries = (new Delivery())->forDriver((int) $user['id']);
        $this->view('deliveries/driver', ['deliveries' => $deliveries]);
    }

    public function driverUpdateStatus(int $id): void
    {
        $user = Auth::user();
        $status = (string) $this->input('status');

        try {
            (new Delivery())->updateStatusByDriver(
                $id,
                (int) $user['id'],
                $status,
                $this->input('lat') !== null ? (float) $this->input('lat') : null,
                $this->input('lng') !== null ? (float) $this->input('lng') : null,
                (string) $this->input('driver_notes', '')
            );
            $this->syncOrderStatusFromDelivery($id, $status);
            (new AuditService())->log('DRIVER_STATUS', 'deliveries', $id, ['status' => $status]);
            Flash::set('success', 'Livraison mise à jour.');
        } catch (\Throwable $e) {
            Flash::set('error', 'Erreur mise à jour livraison: ' . $e->getMessage());
        }

        $this->redirect('/driver/deliveries');
    }

    private function normalizeDateTime(string $raw): ?string
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }

        $timestamp = strtotime($raw);
        if ($timestamp === false) {
            return null;
        }

        return date('Y-m-d H:i:s', $timestamp);
    }

    private function syncOrderStatusFromDelivery(int $deliveryId, string $deliveryStatus): void
    {
        $delivery = (new Delivery())->find($deliveryId);
        if (!$delivery) {
            return;
        }

        $mapping = [
            'pending' => 'prepared',
            'in_transit' => 'shipped',
            'delivered' => 'delivered',
            'failed' => 'prepared',
        ];

        $orderStatus = $mapping[$deliveryStatus] ?? null;
        if ($orderStatus === null) {
            return;
        }

        (new Order())->updateStatus((int) $delivery['order_id'], $orderStatus);
    }
}
