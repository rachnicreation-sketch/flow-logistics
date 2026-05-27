<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Flash;
use App\Models\CustomsDeclaration;
use App\Models\Order;
use App\Models\Purchase;
use App\Services\AuditService;

final class CustomsController extends Controller
{
    public function index(): void
    {
        $this->view('customs/index', [
            'declarations' => (new CustomsDeclaration())->listWithDetails(),
            'orders' => (new Order())->listOrders(),
            'purchases' => (new Purchase())->listPurchases(),
        ]);
    }

    public function store(): void
    {
        $type = (string) $this->input('type');
        $customsOffice = trim((string) $this->input('customs_office'));
        $taxesAmount = (float) $this->input('taxes_amount', 0);
        $orderId = (int) $this->input('order_id') ?: null;
        $purchaseId = (int) $this->input('purchase_id') ?: null;

        if ($type === '' || $customsOffice === '') {
            Flash::set('error', 'Type et bureau douanier sont obligatoires.');
            $this->redirect('/customs');
        }

        try {
            $id = (new CustomsDeclaration())->createDeclaration([
                'order_id' => $orderId,
                'purchase_id' => $purchaseId,
                'type' => $type,
                'customs_office' => $customsOffice,
                'taxes_amount' => $taxesAmount,
            ]);
            (new AuditService())->log('CREATE', 'customs_declarations', $id, ['type' => $type]);
            Flash::set('success', 'Déclaration douanière créée.');
        } catch (\Throwable $e) {
            Flash::set('error', 'Erreur : ' . $e->getMessage());
        }

        $this->redirect('/customs');
    }

    public function updateStatus(int $id): void
    {
        $status = (string) $this->input('status');
        $valid = ['draft', 'submitted', 'cleared', 'rejected'];

        if (!in_array($status, $valid, true)) {
            Flash::set('error', 'Statut invalide.');
            $this->redirect('/customs');
        }

        try {
            (new CustomsDeclaration())->updateStatus($id, $status);
            Flash::set('success', 'Déclaration mise à jour.');
        } catch (\Throwable $e) {
            Flash::set('error', 'Erreur : ' . $e->getMessage());
        }

        $this->redirect('/customs');
    }
}
