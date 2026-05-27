<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Flash;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Order;
use App\Models\Customer;

final class InvoiceController extends Controller
{
    public function index(): void
    {
        $this->view('invoices/index', [
            'invoices' => (new Invoice())->listWithDetails()
        ]);
    }

    public function show(int $id): void
    {
        $invoiceModel = new Invoice();
        $invoice = $invoiceModel->find($id);
        
        if (!$invoice) {
            Flash::set('error', 'Facture introuvable.');
            $this->redirect('/invoices');
        }

        $order = (new Order())->find((int) $invoice['order_id']);
        $items = $order ? (new Order())->items((int) $invoice['order_id']) : [];
        $customer = (new Customer())->find((int) $invoice['customer_id']);
        $payments = (new Payment())->paymentsForInvoice($id);

        $this->view('invoices/show', [
            'invoice' => $invoice,
            'order' => $order,
            'items' => $items,
            'customer' => $customer,
            'payments' => $payments
        ]);
    }

    public function pay(int $id): void
    {
        $amount = (float) $this->input('amount');
        $method = (string) $this->input('payment_method', 'bank_transfer');
        $reference = (string) $this->input('reference', '');

        if ($amount <= 0) {
            Flash::set('error', 'Montant invalide.');
            $this->redirect('/invoices/' . $id);
        }

        $invoiceModel = new Invoice();
        $invoice = $invoiceModel->find($id);

        if (!$invoice) {
            Flash::set('error', 'Facture introuvable.');
            $this->redirect('/invoices');
        }

        try {
            $paymentModel = new Payment();
            $paymentModel->recordPayment([
                'invoice_id' => $id,
                'type' => 'incoming',
                'amount' => $amount,
                'payment_method' => $method,
                'reference' => $reference,
            ]);

            // Calculate total paid
            $payments = $paymentModel->paymentsForInvoice($id);
            $totalPaid = array_sum(array_column($payments, 'amount'));

            $status = 'partially_paid';
            if ($totalPaid >= (float) $invoice['total_incl_tax']) {
                $status = 'paid';
            }

            $invoiceModel->updateStatus($id, $status);

            Flash::set('success', 'Paiement enregistré avec succès.');
        } catch (\Throwable $e) {
            Flash::set('error', 'Erreur lors de l\'enregistrement : ' . $e->getMessage());
        }

        $this->redirect('/invoices/' . $id);
    }
}
