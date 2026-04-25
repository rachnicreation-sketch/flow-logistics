<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Flash;
use App\Models\Company;
use App\Services\AuditService;

final class CompanyController extends Controller
{
    public function index(): void
    {
        $model = new Company();
        $this->view('companies/index', [
            'companies' => $model->all('id DESC'),
            'stats' => $model->stats(),
        ]);
    }

    public function store(): void
    {
        $model = new Company();
        $name = trim((string) $this->input('name'));
        $code = strtoupper(trim((string) $this->input('code')));

        if ($name === '' || $code === '') {
            Flash::set('error', 'Nom et code sont obligatoires.');
            $this->redirect('/companies');
        }

        if ($model->findByCode($code)) {
            Flash::set('error', 'Le code entreprise existe deja.');
            $this->redirect('/companies');
        }

        try {
            $id = $model->create([
                'name' => $name,
                'code' => $code,
                'email' => $this->input('email'),
                'phone' => $this->input('phone'),
                'address' => $this->input('address'),
                'status' => $this->input('status', 'active'),
            ]);
            (new AuditService())->log('CREATE', 'companies', $id, ['name' => $name, 'code' => $code]);
            Flash::set('success', 'Entreprise créée.');
        } catch (\Throwable $e) {
            Flash::set('error', 'Impossible de créer l\'entreprise: ' . $e->getMessage());
        }

        $this->redirect('/companies');
    }
}
