<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Flash;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditService;

final class UserController extends Controller
{
    public function index(): void
    {
        $this->view('users/index', [
            'users' => (new User())->listUsers(),
            'roles' => $this->availableRoles(),
            'companies' => (new Company())->all('id DESC'),
            'canDelete' => $this->isDirectorGeneral(),
        ]);
    }

    public function show(int $id): void
    {
        $model = new User();
        $user = $model->findWithRole($id);
        if (!$user) {
            Flash::set('error', 'Utilisateur introuvable.');
            $this->redirect('/users');
        }

        $this->view('users/show', [
            'targetUser' => $user,
            'roles' => $this->availableRoles(),
            'canDelete' => $this->isDirectorGeneral(),
        ]);
    }

    public function store(): void
    {
        $email = trim((string) $this->input('email'));
        $password = (string) $this->input('password');
        $companyId = $this->resolveCompanyId();

        if ($email === '' || $password === '') {
            Flash::set('error', 'Email et mot de passe sont obligatoires.');
            $this->redirect('/users');
        }

        $model = new User();
        if ($model->findByEmail($email)) {
            Flash::set('error', 'Cet email existe deja.');
            $this->redirect('/users');
        }

        try {
            $roleId = (int) $this->input('role_id');
            $id = $model->createUser([
                'company_id' => $companyId,
                'role_id' => $roleId,
                'name' => $this->input('name'),
                'email' => $email,
                'phone' => $this->input('phone'),
                'password' => $password,
                'is_active' => (int) $this->input('is_active', 1),
            ]);

            (new AuditService())->log('CREATE', 'users', $id, ['email' => $email]);
            Flash::set('success', 'Utilisateur créé.');
        } catch (\Throwable $e) {
            Flash::set('error', 'Impossible de créer l\'utilisateur: ' . $e->getMessage());
        }

        $this->redirect('/users');
    }

    public function update(int $id): void
    {
        $model = new User();
        $target = $model->findWithRole($id);
        if (!$target) {
            Flash::set('error', 'Utilisateur introuvable.');
            $this->redirect('/users');
        }

        if (!$this->canManageTargetUser($target)) {
            Flash::set('error', 'Vous ne pouvez pas modifier le Directeur General.');
            $this->redirect('/users');
        }

        $email = trim((string) $this->input('email'));
        if ($email === '') {
            Flash::set('error', 'Email obligatoire.');
            $this->redirect('/users/' . $id);
        }

        if ($model->emailExistsForAnotherUser($email, $id)) {
            Flash::set('error', 'Cet email est deja utilise.');
            $this->redirect('/users/' . $id);
        }

        try {
            $model->updateUser($id, [
                'company_id' => $this->resolveCompanyId(),
                'role_id' => (int) $this->input('role_id'),
                'name' => $this->input('name'),
                'email' => $email,
                'phone' => $this->input('phone'),
                'password' => (string) $this->input('password', ''),
                'is_active' => (int) $this->input('is_active', 1),
            ]);
            (new AuditService())->log('UPDATE', 'users', $id, ['email' => $email]);
            Flash::set('success', 'Utilisateur mis à jour.');
        } catch (\Throwable $e) {
            Flash::set('error', 'Erreur mise à jour utilisateur: ' . $e->getMessage());
        }

        $this->redirect('/users/' . $id);
    }

    public function delete(int $id): void
    {
        $this->requireDirectorGeneral('/users');

        $model = new User();
        $target = $model->findWithRole($id);
        if (!$target) {
            Flash::set('error', 'Utilisateur introuvable.');
            $this->redirect('/users');
        }

        if ((int) $target['id'] === (int) (Auth::id() ?? 0)) {
            Flash::set('error', 'Vous ne pouvez pas supprimer votre propre compte.');
            $this->redirect('/users/' . $id);
        }

        try {
            $model->deleteUser($id);
            (new AuditService())->log('DELETE', 'users', $id);
            Flash::set('success', 'Utilisateur supprime.');
            $this->redirect('/users');
        } catch (\Throwable $e) {
            Flash::set('error', 'Suppression impossible: ' . $e->getMessage());
            $this->redirect('/users/' . $id);
        }
    }

    public function toggle(int $id): void
    {
        $model = new User();
        $target = $model->findWithRole($id);
        if (!$target) {
            Flash::set('error', 'Utilisateur introuvable.');
            $this->redirect('/users');
        }

        if (!$this->canManageTargetUser($target)) {
            Flash::set('error', 'Vous ne pouvez pas modifier le Directeur General.');
            $this->redirect('/users');
        }

        $newStatus = (int) !(int) $target['is_active'];
        $model->setStatus($id, $newStatus);
        (new AuditService())->log('TOGGLE_STATUS', 'users', $id, ['active' => $newStatus]);
        Flash::set('success', 'Statut utilisateur mis à jour.');
        $this->redirect('/users');
    }

    private function availableRoles(): array
    {
        $roles = (new Role())->allRoles();
        return array_values(array_filter(
            $roles,
            static fn (array $r): bool => in_array((string) $r['slug'], ['dg', 'dm', 'company_admin', 'logistics_manager', 'storekeeper', 'driver'], true)
        ));
    }

    private function canManageTargetUser(array $target): bool
    {
        $targetRole = (string) ($target['role_slug'] ?? '');
        if ($targetRole !== 'dg') {
            return true;
        }

        return $this->isDirectorGeneral();
    }

    private function resolveCompanyId(): ?int
    {
        if (Auth::companyId() !== null) {
            return (int) Auth::companyId();
        }

        $companies = (new Company())->all('id ASC');
        if (empty($companies)) {
            return null;
        }

        return (int) $companies[0]['id'];
    }
}
