<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Flash;
use App\Models\ReturnAuthorization;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Product;
use App\Services\AuditService;

final class ReturnController extends Controller
{
    public function index(): void
    {
        $this->view('returns/index', [
            'returns' => (new ReturnAuthorization())->listWithDetails(),
            'orders' => (new Order())->listOrders(),
            'products' => (new Product())->all('name ASC'),
        ]);
    }

    public function store(): void
    {
        $orderId = (int) $this->input('order_id');
        $reason = trim((string) $this->input('reason'));

        if ($orderId <= 0 || $reason === '') {
            Flash::set('error', 'Commande et motif de retour obligatoires.');
            $this->redirect('/returns');
        }

        $order = (new Order())->find($orderId);
        if (!$order) {
            Flash::set('error', 'Commande introuvable.');
            $this->redirect('/returns');
        }

        $productIds = $_POST['product_id'] ?? [];
        $quantities = $_POST['quantity'] ?? [];
        $conditions = $_POST['condition_status'] ?? [];

        $items = [];
        foreach ($productIds as $i => $pid) {
            $pid = (int) $pid;
            $qty = (float) ($quantities[$i] ?? 0);
            $cond = $conditions[$i] ?? 'resellable';
            if ($pid > 0 && $qty > 0) {
                $items[] = [
                    'product_id' => $pid,
                    'quantity' => $qty,
                    'condition_status' => $cond
                ];
            }
        }

        if (empty($items)) {
            Flash::set('error', 'Ajoutez au moins un article à retourner.');
            $this->redirect('/returns');
        }

        try {
            $id = (new ReturnAuthorization())->createReturn([
                'order_id' => $orderId,
                'customer_id' => $order['customer_id'],
                'reason' => $reason
            ], $items);

            (new AuditService())->log('CREATE', 'return_authorizations', $id, ['order_id' => $orderId]);
            Flash::set('success', 'Retour créé et en attente de validation.');
        } catch (\Throwable $e) {
            Flash::set('error', 'Erreur : ' . $e->getMessage());
        }

        $this->redirect('/returns');
    }

    public function updateStatus(int $id): void
    {
        $status = (string) $this->input('status');
        $valid = ['requested', 'approved', 'received', 'inspected', 'refunded', 'rejected'];

        if (!in_array($status, $valid, true)) {
            Flash::set('error', 'Statut invalide.');
            $this->redirect('/returns');
        }

        try {
            (new ReturnAuthorization())->updateStatus($id, $status);
            (new AuditService())->log('UPDATE', 'return_authorizations', $id, ['status' => $status]);
            Flash::set('success', 'Retour mis à jour.');
        } catch (\Throwable $e) {
            Flash::set('error', 'Erreur : ' . $e->getMessage());
        }

        $this->redirect('/returns');
    }
}
