<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Flash;
use App\Models\Supplier;
use App\Services\AuditService;

final class SupplierController extends Controller
{
    public function index(): void
    {
        $this->view('suppliers/index', [
            'suppliers' => (new Supplier())->all('id DESC'),
            'canDelete' => $this->isDirectorGeneral(),
        ]);
    }

    public function show(int $id): void
    {
        $model = new Supplier();
        $supplier = $model->find($id);
        if (!$supplier) {
            Flash::set('error', 'Fournisseur introuvable.');
            $this->redirect('/suppliers');
        }

        $this->view('suppliers/show', [
            'supplier' => $supplier,
            'history' => $model->history($id),
            'canDelete' => $this->isDirectorGeneral(),
        ]);
    }

    public function store(): void
    {
        $name = trim((string) $this->input('name'));
        if ($name === '') {
            Flash::set('error', 'Le nom fournisseur est obligatoire.');
            $this->redirect('/suppliers');
        }

        try {
            $id = (new Supplier())->createSupplier([
                'name' => $name,
                'contact_name' => $this->input('contact_name'),
                'email' => $this->input('email'),
                'phone' => $this->input('phone'),
                'address' => $this->input('address'),
                'rating' => (float) $this->input('rating', 0),
                'status' => $this->input('status', 'active'),
            ]);

            (new AuditService())->log('CREATE', 'suppliers', $id, ['name' => $name]);
            Flash::set('success', 'Fournisseur ajoute.');
        } catch (\Throwable $e) {
            Flash::set('error', 'Impossible d\'ajouter le fournisseur: ' . $e->getMessage());
        }

        $this->redirect('/suppliers');
    }

    public function update(int $id): void
    {
        $model = new Supplier();
        $supplier = $model->find($id);
        if (!$supplier) {
            Flash::set('error', 'Fournisseur introuvable.');
            $this->redirect('/suppliers');
        }

        $name = trim((string) $this->input('name'));
        if ($name === '') {
            Flash::set('error', 'Nom fournisseur obligatoire.');
            $this->redirect('/suppliers/' . $id);
        }

        try {
            $model->updateSupplier($id, [
                'name' => $name,
                'contact_name' => $this->input('contact_name'),
                'email' => $this->input('email'),
                'phone' => $this->input('phone'),
                'address' => $this->input('address'),
                'rating' => (float) $this->input('rating', 0),
                'status' => $this->input('status', 'active'),
            ]);
            (new AuditService())->log('UPDATE', 'suppliers', $id, ['name' => $name]);
            Flash::set('success', 'Fournisseur mis à jour.');
        } catch (\Throwable $e) {
            Flash::set('error', 'Erreur mise à jour fournisseur: ' . $e->getMessage());
        }

        $this->redirect('/suppliers/' . $id);
    }

    public function delete(int $id): void
    {
        $this->requireDirectorGeneral('/suppliers');

        try {
            (new Supplier())->deleteSupplier($id);
            (new AuditService())->log('DELETE', 'suppliers', $id);
            Flash::set('success', 'Fournisseur supprime.');
            $this->redirect('/suppliers');
        } catch (\Throwable $e) {
            Flash::set('error', 'Suppression impossible: ' . $e->getMessage());
            $this->redirect('/suppliers/' . $id);
        }
    }

    public function history(int $id): void
    {
        $model = new Supplier();
        $supplier = $model->find($id);
        if (!$supplier) {
            Flash::set('error', 'Fournisseur introuvable.');
            $this->redirect('/suppliers');
        }

        $this->view('suppliers/history', [
            'supplier' => $supplier,
            'history' => $model->history($id),
        ]);
    }
}
