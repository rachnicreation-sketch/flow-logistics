<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Flash;
use App\Models\Category;
use App\Models\Product;
use App\Services\AuditService;

final class ProductController extends Controller
{
    public function index(): void
    {
        $this->view('products/index', [
            'products' => (new Product())->listWithCategory(),
            'categories' => (new Category())->all('id DESC'),
            'lowStock' => (new Product())->lowStock(),
            'forecast' => (new Product())->forecastNeeds(),
            'canDelete' => $this->isDirectorGeneral(),
        ]);
    }

    public function show(int $id): void
    {
        $model = new Product();
        $product = $model->detail($id);
        if (!$product) {
            Flash::set('error', 'Produit introuvable.');
            $this->redirect('/products');
        }

        $this->view('products/show', [
            'product' => $product,
            'categories' => (new Category())->all('id DESC'),
            'stocks' => $model->stockByWarehouse($id),
            'canDelete' => $this->isDirectorGeneral(),
        ]);
    }

    public function storeCategory(): void
    {
        $name = trim((string) $this->input('name'));
        if ($name === '') {
            Flash::set('error', 'Nom categorie obligatoire.');
            $this->redirect('/products');
        }

        try {
            $id = (new Category())->createCategory([
                'name' => $name,
                'description' => $this->input('description'),
            ]);
            (new AuditService())->log('CREATE', 'categories', $id, ['name' => $name]);
            Flash::set('success', 'Categorie créée.');
        } catch (\Throwable $e) {
            Flash::set('error', 'Impossible de créer la categorie: ' . $e->getMessage());
        }

        $this->redirect('/products');
    }

    public function store(): void
    {
        $name = trim((string) $this->input('name'));
        $sku = trim((string) $this->input('sku'));
        if ($sku === '') {
            $sku = 'SKU-' . date('YmdHis') . random_int(10, 99);
        }
        if ($name === '') {
            Flash::set('error', 'Nom obligatoire.');
            $this->redirect('/products');
        }

        try {
            $id = (new Product())->createProduct([
                'category_id' => $this->input('category_id'),
                'name' => $name,
                'sku' => $sku,
                'barcode' => $this->input('barcode'),
                'unit' => $this->input('unit'),
                'purchase_price' => (float) $this->input('purchase_price', 0),
                'sale_price' => (float) $this->input('sale_price', 0),
                'min_stock' => (float) $this->input('min_stock', 0),
                'status' => $this->input('status', 'active'),
            ]);
            (new AuditService())->log('CREATE', 'products', $id, ['sku' => $sku]);
            Flash::set('success', 'Produit créé (code-barres genere si vide).');
        } catch (\Throwable $e) {
            Flash::set('error', 'Impossible de créer le produit: ' . $e->getMessage());
        }

        $this->redirect('/products');
    }

    public function update(int $id): void
    {
        $model = new Product();
        $product = $model->find($id);
        if (!$product) {
            Flash::set('error', 'Produit introuvable.');
            $this->redirect('/products');
        }

        $name = trim((string) $this->input('name'));
        $sku = trim((string) $this->input('sku'));
        if ($sku === '') {
            $sku = 'SKU-' . date('YmdHis') . random_int(10, 99);
        }
        if ($name === '') {
            Flash::set('error', 'Nom obligatoire.');
            $this->redirect('/products/' . $id);
        }

        try {
            $model->updateProduct($id, [
                'category_id' => $this->input('category_id'),
                'name' => $name,
                'sku' => $sku,
                'barcode' => $this->input('barcode', ''),
                'unit' => $this->input('unit'),
                'purchase_price' => (float) $this->input('purchase_price', 0),
                'sale_price' => (float) $this->input('sale_price', 0),
                'min_stock' => (float) $this->input('min_stock', 0),
                'status' => $this->input('status', 'active'),
            ]);
            (new AuditService())->log('UPDATE', 'products', $id, ['sku' => $sku]);
            Flash::set('success', 'Produit mis à jour.');
        } catch (\Throwable $e) {
            Flash::set('error', 'Erreur mise à jour produit: ' . $e->getMessage());
        }

        $this->redirect('/products/' . $id);
    }

    public function deleteBarcode(int $id): void
    {
        try {
            (new Product())->clearBarcode($id);
            (new AuditService())->log('DELETE_BARCODE', 'products', $id);
            Flash::set('success', 'Code-barres supprime.');
        } catch (\Throwable $e) {
            Flash::set('error', 'Erreur suppression code-barres: ' . $e->getMessage());
        }

        $this->redirect('/products/' . $id);
    }

    public function delete(int $id): void
    {
        $this->requireDirectorGeneral('/products');

        try {
            (new Product())->deleteProduct($id);
            (new AuditService())->log('DELETE', 'products', $id);
            Flash::set('success', 'Produit supprime.');
            $this->redirect('/products');
        } catch (\Throwable $e) {
            Flash::set('error', 'Suppression impossible: ' . $e->getMessage());
            $this->redirect('/products/' . $id);
        }
    }
}
