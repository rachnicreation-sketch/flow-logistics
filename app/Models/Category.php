<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Category extends Model
{
    protected string $table = 'categories';

    public function createCategory(array $data): int
    {
        return $this->insert([
            'company_id' => $this->currentCompanyId(),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}

