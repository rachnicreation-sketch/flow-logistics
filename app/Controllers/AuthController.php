<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Flash;
use App\Services\AuditService;

final class AuthController extends Controller
{
    public function showLogin(): void
    {
        $this->view('auth/login', [], 'auth');
    }

    public function login(): void
    {
        $email = trim((string) $this->input('email'));
        $password = (string) $this->input('password');

        if (Auth::attempt($email, $password)) {
            session_regenerate_id(true);
            (new AuditService())->log('LOGIN_SUCCESS', 'auth', Auth::id());
            $this->redirect('/dashboard');
        }

        Flash::set('error', 'Identifiants invalides ou compte inactif.');
        (new AuditService())->log('LOGIN_FAILED', 'auth', null, ['email' => $email]);
        $this->redirect('/login');
    }

    public function logout(): void
    {
        (new AuditService())->log('LOGOUT', 'auth', Auth::id());
        Auth::logout();
        $this->redirect('/login');
    }
}

