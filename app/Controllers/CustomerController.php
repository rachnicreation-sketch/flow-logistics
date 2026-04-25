<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Flash;
use App\Models\Customer;
use App\Services\AuditService;

final class CustomerController extends Controller
{
    public function index(): void
    {
        $this->view('customers/index', [
            'customers' => (new Customer())->all('id DESC'),
            'canDelete' => $this->isDirectorGeneral(),
        ]);
    }

    public function show(int $id): void
    {
        $model = new Customer();
        $customer = $model->find($id);
        if (!$customer) {
            Flash::set('error', 'Client introuvable.');
            $this->redirect('/customers');
        }

        $this->view('customers/show', [
            'customer' => $customer,
            'canDelete' => $this->isDirectorGeneral(),
        ]);
    }

    public function store(): void
    {
        $name = trim((string) $this->input('name'));
        if ($name === '') {
            Flash::set('error', 'Nom client obligatoire.');
            $this->redirect('/customers');
        }

        try {
            $id = (new Customer())->createCustomer([
                'name' => $name,
                'email' => $this->input('email'),
                'phone' => $this->input('phone'),
                'address' => $this->input('address'),
            ]);
            (new AuditService())->log('CREATE', 'customers', $id, ['name' => $name]);
            Flash::set('success', 'Client ajoute.');
        } catch (\Throwable $e) {
            Flash::set('error', 'Impossible d\'ajouter le client: ' . $e->getMessage());
        }

        $this->redirect('/customers');
    }

    public function update(int $id): void
    {
        $model = new Customer();
        $customer = $model->find($id);
        if (!$customer) {
            Flash::set('error', 'Client introuvable.');
            $this->redirect('/customers');
        }

        $name = trim((string) $this->input('name'));
        if ($name === '') {
            Flash::set('error', 'Nom client obligatoire.');
            $this->redirect('/customers/' . $id);
        }

        try {
            $model->updateCustomer($id, [
                'name' => $name,
                'email' => $this->input('email'),
                'phone' => $this->input('phone'),
                'address' => $this->input('address'),
            ]);
            (new AuditService())->log('UPDATE', 'customers', $id, ['name' => $name]);
            Flash::set('success', 'Client mis à jour.');
        } catch (\Throwable $e) {
            Flash::set('error', 'Erreur mise à jour client: ' . $e->getMessage());
        }

        $this->redirect('/customers/' . $id);
    }

    public function delete(int $id): void
    {
        $this->requireDirectorGeneral('/customers');

        try {
            (new Customer())->deleteCustomer($id);
            (new AuditService())->log('DELETE', 'customers', $id);
            Flash::set('success', 'Client supprime.');
            $this->redirect('/customers');
        } catch (\Throwable $e) {
            Flash::set('error', 'Suppression impossible: ' . $e->getMessage());
            $this->redirect('/customers/' . $id);
        }
    }
}
