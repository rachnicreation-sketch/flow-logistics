<?php

declare(strict_types=1);

namespace App\Core;

class Controller
{
    protected function view(string $view, array $data = [], string $layout = 'main'): void
    {
        echo View::render($view, $data, $layout);
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . url($path));
        exit;
    }

    protected function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    protected function roleSlug(): string
    {
        return (string) (Auth::user()['role_slug'] ?? '');
    }

    protected function isDirectorGeneral(): bool
    {
        return in_array($this->roleSlug(), ['dg', 'super_admin'], true);
    }

    protected function isDirectorManager(): bool
    {
        return in_array($this->roleSlug(), ['dm', 'company_admin'], true);
    }

    protected function requireDirectorGeneral(string $redirectPath, string $message = 'Seul le Directeur General peut supprimer.'): void
    {
        if ($this->isDirectorGeneral()) {
            return;
        }

        Flash::set('error', $message);
        $this->redirect($redirectPath);
    }
}
