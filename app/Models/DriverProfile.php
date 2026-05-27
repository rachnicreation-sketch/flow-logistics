<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

final class DriverProfile extends Model
{
    protected string $table = 'driver_profiles';

    public function listWithUsers(): array
    {
        // On récupère tous les utilisateurs ayant le rôle 'driver' (ou via leur slug)
        // et on fait une jointure gauche (LEFT JOIN) avec driver_profiles
        // au cas où le profil n'a pas encore été créé pour ce chauffeur.
        $sql = "SELECT u.id AS user_id, u.name, u.email, u.phone,
                       dp.id AS profile_id, dp.license_number, dp.license_type, dp.license_expiry,
                       dp.total_hours, dp.bonuses, dp.penalties, dp.status
                FROM users u
                INNER JOIN roles r ON r.id = u.role_id
                LEFT JOIN driver_profiles dp ON dp.user_id = u.id
                WHERE r.slug = 'driver' AND u.is_active = 1";
        
        if (!$this->isSuperAdmin()) {
            $sql .= ' AND u.company_id = :company_id';
        }
        $sql .= ' ORDER BY u.name ASC';

        $stmt = $this->db->prepare($sql);
        if (!$this->isSuperAdmin()) {
            $stmt->bindValue(':company_id', $this->currentCompanyId(), PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function upsertProfile(int $userId, array $data): bool
    {
        $sqlCheck = 'SELECT id FROM driver_profiles WHERE user_id = :user_id';
        $stmtCheck = $this->db->prepare($sqlCheck);
        $stmtCheck->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmtCheck->execute();
        $existingId = $stmtCheck->fetchColumn();

        if ($existingId) {
            // Update
            return $this->updateById((int) $existingId, [
                'license_number' => $data['license_number'],
                'license_type' => $data['license_type'],
                'license_expiry' => $data['license_expiry'],
                'total_hours' => $data['total_hours'] ?? 0,
                'bonuses' => $data['bonuses'] ?? 0,
                'penalties' => $data['penalties'] ?? 0,
                'status' => $data['status'] ?? 'available'
            ]);
        }

        // Insert
        $this->insert([
            'user_id' => $userId,
            'license_number' => $data['license_number'],
            'license_type' => $data['license_type'],
            'license_expiry' => $data['license_expiry'],
            'total_hours' => $data['total_hours'] ?? 0,
            'bonuses' => $data['bonuses'] ?? 0,
            'penalties' => $data['penalties'] ?? 0,
            'status' => $data['status'] ?? 'available'
        ]);
        return true;
    }

    public function updateStatus(int $userId, string $status): bool
    {
        $sql = 'UPDATE driver_profiles SET status = :status WHERE user_id = :user_id';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':status', $status);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
