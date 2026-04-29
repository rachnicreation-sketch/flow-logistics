<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Flash;
use App\Models\Category;
use App\Services\AuditService;

final class CategoryController extends Controller
{
    public function index(): void
    {
        $this->view('categories/index', [
            'categories' => (new Category())->all('name ASC'),
        ]);
    }

    public function store(): void
    {
        $name = trim((string) $this->input('name'));
        if ($name === '') {
            Flash::set('error', 'Le nom de la catégorie est obligatoire.');
            $this->redirect('/categories');
        }

        try {
            $id = (new Category())->createCategory([
                'name' => $name,
                'description' => $this->input('description'),
            ]);
            (new AuditService())->log('CREATE', 'categories', $id, ['name' => $name]);
            Flash::set('success', 'Catégorie créée.');
        } catch (\Throwable $e) {
            Flash::set('error', 'Impossible de créer la catégorie : ' . $e->getMessage());
        }

        $this->redirect('/categories');
    }

    public function update(int $id): void
    {
        $name = trim((string) $this->input('name'));
        if ($name === '') {
            Flash::set('error', 'Le nom de la catégorie est obligatoire.');
            $this->redirect('/categories');
        }

        try {
            (new Category())->updateCategory($id, [
                'name' => $name,
                'description' => $this->input('description'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            (new AuditService())->log('UPDATE', 'categories', $id, ['name' => $name]);
            Flash::set('success', 'Catégorie mise à jour.');
        } catch (\Throwable $e) {
            Flash::set('error', 'Impossible de mettre à jour la catégorie : ' . $e->getMessage());
        }

        $this->redirect('/categories');
    }

    public function delete(int $id): void
    {
        try {
            (new Category())->deleteCategory($id);
            (new AuditService())->log('DELETE', 'categories', $id);
            Flash::set('success', 'Catégorie supprimée.');
        } catch (\Throwable $e) {
            Flash::set('error', 'Impossible de supprimer la catégorie : ' . $e->getMessage());
        }
        $this->redirect('/categories');
    }
}
