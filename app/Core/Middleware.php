<?php

declare(strict_types=1);

namespace App\Core;

final class Middleware
{
    public static function handle(array $middlewares): void
    {
        foreach ($middlewares as $middleware) {
            if ($middleware === 'auth' && !Auth::check()) {
                Flash::set('error', 'Veuillez vous connecter.');
                header('Location: ' . url('/login'));
                exit;
            }

            if ($middleware === 'guest' && Auth::check()) {
                header('Location: ' . url('/dashboard'));
                exit;
            }

            if (str_starts_with($middleware, 'permission:')) {
                $permission = explode(':', $middleware, 2)[1] ?? '';
                if (!Auth::can($permission)) {
                    http_response_code(403);
                    echo View::render('errors/403', ['permission' => $permission], 'empty');
                    exit;
                }
            }

            if ($middleware === 'csrf' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
                if (!Csrf::check($_POST['_token'] ?? null)) {
                    http_response_code(419);
                    exit('Jeton CSRF invalide.');
                }
            }

            if ($middleware === 'driver.delivery.access') {
                $user = Auth::user();
                if (!$user || !Auth::can('deliveries.driver')) {
                    http_response_code(403);
                    exit('AccÃƒÂ¨s interdit.');
                }
            }
        }
    }
}
