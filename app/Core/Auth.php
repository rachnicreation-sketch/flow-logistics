<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\User;

final class Auth
{
    public static function check(): bool
    {
        return isset($_SESSION['auth_user']);
    }

    public static function user(): ?array
    {
        return $_SESSION['auth_user'] ?? null;
    }

    public static function id(): ?int
    {
        return self::user()['id'] ?? null;
    }

    public static function companyId(): ?int
    {
        return self::user()['company_id'] ?? null;
    }

    public static function login(array $user): void
    {
        $_SESSION['auth_user'] = [
            'id' => (int) $user['id'],
            'company_id' => isset($user['company_id']) ? (int) $user['company_id'] : null,
            'role_id' => (int) $user['role_id'],
            'role_slug' => $user['role_slug'] ?? '',
            'name' => $user['name'],
            'email' => $user['email'],
            'permissions' => $user['permissions'] ?? [],
        ];
    }

    public static function attempt(string $email, string $password): bool
    {
        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        if (!(int) $user['is_active']) {
            return false;
        }

        $user['permissions'] = $userModel->permissions((int) $user['role_id']);
        self::login($user);
        $userModel->touchLastLogin((int) $user['id']);
        return true;
    }

    public static function logout(): void
    {
        unset($_SESSION['auth_user']);
        session_regenerate_id(true);
    }

    public static function can(string $permission): bool
    {
        $user = self::user();
        if (!$user) {
            return false;
        }

        if (($user['role_slug'] ?? '') === 'super_admin') {
            return true;
        }

        return in_array($permission, $user['permissions'] ?? [], true);
    }
}

