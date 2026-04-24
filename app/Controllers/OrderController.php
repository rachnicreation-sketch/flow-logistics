<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Flash;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\AuditService;
use App\Services\NotificationService;
use App\Services\StockService;

final class OrderController extends Controller
{
    public function index(): void
    {
        $this->view('orders/index', [
            'orders' => (new Order())->listOrders(),
            'customers' => (new Customer())->all('id DESC'),
            'products' => (new Product())->all('id DESC'),
            'warehouses' => (new Warehouse())->all('id DESC'),
        ]);
    }

    public function storeCustomer(): void
    {
        $name = trim((string) $this->input('name'));
        if ($name === '') {
            Flash::set('error', 'Nom client obligatoire.');
            $this->redirect('/orders');
        }

        try {
            $id = (new Customer())->createCustomer([
                'name' => $name,
                'email' => $this->input('email'),
                'phone' => $this->input('phone'),
                'address' => $this->input('address'),
            ]);
            (new AuditService())->log('CREATE', 'customers', $id, ['name' => $name]);
            Flash::set('success', 'Client crÃƒÂ©ÃƒÂ©.');
        } catch (\Throwable $e) {
            Flash::set('error', 'Impossible de crÃƒÂ©er le client: ' . $e->getMessage());
        }

        $this->redirect('/orders');
    }

    public function store(): void
    {
        $customerId = (int) $this->input('customer_id');
        $reference = trim((string) $this->input('reference'));
        if ($customerId <= 0 || $reference === '') {
            Flash::set('error', 'Client et rÃƒÂ©fÃƒÂ©rence commande sont obligatoires.');
            $this->redirect('/orders');
        }

        $items = $this->extractItems();
        if (empty($items)) {
            Flash::set('error', 'Ajoutez au moins un produit.');
            $this->redirect('/orders');
        }

        try {
            $total = array_sum(array_map(static fn (array $i): float => $i['quantity'] * $i['unit_price'], $items));
            $invoice = 'INV-' . date('Ymd') . '-' . random_int(1000, 9999);
            $id = (new Order())->createOrder([
                'customer_id' => $customerId,
                'reference' => $reference,
                'status' => 'pending',
                'invoice_number' => $invoice,
                'delivery_address' => $this->input('delivery_address'),
                'total_amount' => $total,
            ], $items);

            $customer = (new Customer())->find($customerId);
            if ($customer && !empty($customer['email'])) {
                (new NotificationService())->orderConfirmation($customer['email'], $reference);
            }

            (new AuditService())->log('CREATE', 'orders', $id, ['reference' => $reference]);
            Flash::set('success', 'Commande client crÃƒÂ©ÃƒÂ©e.');
        } catch (\Throwable $e) {
            Flash::set('error', 'Impossible de crÃƒÂ©er la commande: ' . $e->getMessage());
        }

        $this->redirect('/orders');
    }

    public function validate(int $id): void
    {
        try {
            (new Order())->updateStatus($id, 'validated');
            (new AuditService())->log('VALIDATE', 'orders', $id);
            Flash::set('success', 'Commande validÃƒÂ©e.');
        } catch (\Throwable $e) {
            Flash::set('error', 'Erreur validation commande: ' . $e->getMessage());
        }

        $this->redirect('/orders');
    }

    public function prepare(int $id): void
    {
        $warehouseId = (int) $this->input('warehouse_id');
        $method = (string) $this->input('method', 'FIFO');
        if ($warehouseId <= 0) {
            Flash::set('error', 'EntrepÃƒÂ´t requis.');
            $this->redirect('/orders');
        }
        try {
            (new StockService())->fulfillOrder($id, $warehouseId, null, $method);
            (new AuditService())->log('PREPARE', 'orders', $id, ['warehouse_id' => $warehouseId, 'method' => $method]);
            Flash::set('success', 'Commande prÃƒÂ©parÃƒÂ©e, stock dÃƒÂ©crÃƒÂ©mentÃƒÂ©.');
        } catch (\Throwable $e) {
            Flash::set('error', 'Erreur prÃƒÂ©paration: ' . $e->getMessage());
        }
        $this->redirect('/orders');
    }

    public function invoice(int $id): void
    {
        $orderModel = new Order();
        $order = $orderModel->find($id);
        if (!$order) {
            Flash::set('error', 'Commande introuvable.');
            $this->redirect('/orders');
        }

        $items = $orderModel->items($id);
        $customer = (new Customer())->find((int) $order['customer_id']);
        $this->view('orders/invoice', [
            'order' => $order,
            'items' => $items,
            'customer' => $customer,
        ], 'empty');
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
