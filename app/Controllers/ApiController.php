<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
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
        if (!Auth::attempt($email, $password)) {
            $this->json(['error' => 'Identifiants invalides'], 401);
        }
        $user = Auth::user();
        $token = (new ApiToken())->issue((int) $user['company_id'], (int) $user['id']);
        $this->json([
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'role' => $user['role_slug'],
            ],
        ]);
    }

    public function driverDeliveries(): void
    {
        $user = $this->authToken();
        if (!$user) {
            return;
        }
        if (($user['role_slug'] ?? '') !== 'driver' && ($user['role_slug'] ?? '') !== 'super_admin') {
            $this->json(['error' => 'RÃƒÂ´le non autorisÃƒÂ©'], 403);
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
        if (($user['role_slug'] ?? '') !== 'driver' && ($user['role_slug'] ?? '') !== 'super_admin') {
            $this->json(['error' => 'RÃƒÂ´le non autorisÃƒÂ©'], 403);
        }
        $payload = $this->jsonInput();
        $status = (string) ($payload['status'] ?? '');
        if (!in_array($status, ['pending', 'in_transit', 'delivered'], true)) {
            $this->json(['error' => 'Statut invalide'], 422);
        }
        $deliveryModel = new Delivery();
        if (($user['role_slug'] ?? '') === 'driver') {
            $deliveryModel->updateStatusByDriver(
                $id,
                (int) $user['id'],
                $status,
                isset($payload['lat']) ? (float) $payload['lat'] : null,
                isset($payload['lng']) ? (float) $payload['lng'] : null,
                (string) ($payload['notes'] ?? '')
            );
        } else {
            $deliveryModel->updateStatus(
                $id,
                $status,
                isset($payload['lat']) ? (float) $payload['lat'] : null,
                isset($payload['lng']) ? (float) $payload['lng'] : null,
                (string) ($payload['notes'] ?? '')
            );
        }
        $this->json(['message' => 'Statut mis ÃƒÂ  jour']);
    }

    private function authToken(): ?array
    {
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? ($_SERVER['HTTP_AUTHORIZATION'] ?? '');
        if (!preg_match('/Bearer\s+(.+)/i', (string) $authHeader, $matches)) {
            $this->json(['error' => 'Token manquant'], 401);
        }
        $plainToken = trim($matches[1]);
        $tokenRow = (new ApiToken())->resolveUser($plainToken);
        if (!$tokenRow) {
            $this->json(['error' => 'Token invalide'], 401);
        }

        $stmt = Database::connection()->prepare(
            'SELECT u.*, r.slug AS role_slug
             FROM users u
             INNER JOIN roles r ON r.id = u.role_id
             WHERE u.id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $tokenRow['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            $this->json(['error' => 'Utilisateur introuvable'], 404);
        }

        $permissions = (new User())->permissions((int) $user['role_id']);
        Auth::login([
            'id' => (int) $user['id'],
            'company_id' => (int) $user['company_id'],
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
}
