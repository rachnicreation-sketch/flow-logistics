<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\RateLimiter;
use App\Models\ApiToken;
use App\Models\Delivery;
use App\Models\User;
use PDO;

final class ApiController extends Controller
{
    public function login(): void
    {
        $payload = $this->jsonInput();
        $email = trim((string) ($payload['email'] ?? ''));
        $password = (string) ($payload['password'] ?? '');
        $key = 'api-login|' . strtolower($email) . '|' . $this->clientIp();

        if (RateLimiter::tooManyAttempts($key, 8, 900)) {
            $this->json([
                'error' => 'Too many login attempts',
                'retry_after' => RateLimiter::availableIn($key),
            ], 429);
        }

        if (!Auth::attempt($email, $password)) {
            RateLimiter::hit($key, 900);
            $this->json(['error' => 'Identifiants invalides'], 401);
        }

        $user = Auth::user();
        if (!Auth::can('api.driver')) {
            Auth::logout();
            $this->json(['error' => 'Compte non autorisé pour l API chauffeur'], 403);
        }

        $companyId = (int) ($user['company_id'] ?? 0);
        if ($companyId <= 0) {
            Auth::logout();
            $this->json(['error' => 'Compte sans entreprise associée'], 422);
        }

        RateLimiter::clear($key);
        $token = (new ApiToken())->issue($companyId, (int) $user['id']);
        $this->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => 86400,
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'role' => $user['role_slug'],
            ],
        ]);
    }

    public function logout(): void
    {
        $plainToken = $this->parseBearerToken();
        (new ApiToken())->revoke($plainToken);
        Auth::logout();
        $this->json(['message' => 'Logout effectué']);
    }

    public function driverDeliveries(): void
    {
        $user = $this->authToken();
        if (!$user) {
            return;
        }

        if (!Auth::can('api.driver')) {
            $this->json(['error' => 'Role non autorisé'], 403);
        }

        $deliveries = (new Delivery())->forDriver((int) $user['id']);
        $this->json(['data' => $deliveries]);
    }

    public function updateDriverDeliveryStatus(int $id): void
    {
        $user = $this->authToken();
        if (!$user) {
            return;
        }

        if (!Auth::can('api.driver')) {
            $this->json(['error' => 'Role non autorisé'], 403);
        }

        $payload = $this->jsonInput();
        $status = (string) ($payload['status'] ?? '');
        if (!in_array($status, ['pending', 'in_transit', 'delivered'], true)) {
            $this->json(['error' => 'Statut invalide'], 422);
        }

        (new Delivery())->updateStatusByDriver(
            $id,
            (int) $user['id'],
            $status,
            isset($payload['lat']) ? (float) $payload['lat'] : null,
            isset($payload['lng']) ? (float) $payload['lng'] : null,
            (string) ($payload['notes'] ?? '')
        );

        $this->json(['message' => 'Statut mis à jour']);
    }

    private function authToken(): ?array
    {
        $plainToken = $this->parseBearerToken();
        $tokenRow = (new ApiToken())->resolveUser($plainToken);
        if (!$tokenRow) {
            $this->json(['error' => 'Token invalide'], 401);
        }

        $stmt = Database::connection()->prepare(
            'SELECT u.*, r.slug AS role_slug
             FROM users u
             INNER JOIN roles r ON r.id = u.role_id
             WHERE u.id = :id
               AND u.is_active = 1
               AND u.company_id = :company_id
             LIMIT 1'
        );
        $stmt->execute([
            'id' => $tokenRow['user_id'],
            'company_id' => $tokenRow['company_id'],
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            $this->json(['error' => 'Utilisateur inactif ou introuvable'], 401);
        }

        $permissions = (new User())->permissions((int) $user['role_id']);
        Auth::login([
            'id' => (int) $user['id'],
            'company_id' => isset($user['company_id']) ? (int) $user['company_id'] : null,
            'role_id' => (int) $user['role_id'],
            'role_slug' => $user['role_slug'],
            'name' => $user['name'],
            'email' => $user['email'],
            'permissions' => $permissions,
        ]);

        return Auth::user();
    }

    private function jsonInput(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw === false || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function parseBearerToken(): string
    {
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? ($_SERVER['HTTP_AUTHORIZATION'] ?? '');

        if (!preg_match('/Bearer\s+(.+)/i', (string) $authHeader, $matches)) {
            $this->json(['error' => 'Token manquant'], 401);
        }

        return trim((string) $matches[1]);
    }

    private function clientIp(): string
    {
        return (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    }
}
