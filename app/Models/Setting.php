<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

final class Setting extends Model
{
    protected string $table = 'company_settings';

    public function allSettings(): array
    {
        $stmt = $this->db->prepare('SELECT setting_key, setting_value FROM company_settings WHERE company_id = :company_id');
        $stmt->execute(['company_id' => $this->currentCompanyId()]);
        $rows = $stmt->fetchAll();
        $out = [];
        foreach ($rows as $row) {
            $out[$row['setting_key']] = $row['setting_value'];
        }
        return $out;
    }

    public function upsert(string $key, string $value): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO company_settings (company_id, setting_key, setting_value, updated_at)
             VALUES (:company_id, :setting_key, :setting_value, :updated_at)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = VALUES(updated_at)'
        );
        $stmt->execute([
            'company_id' => $this->currentCompanyId(),
            'setting_key' => $key,
            'setting_value' => $value,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }
}

