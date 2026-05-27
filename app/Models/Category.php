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
            'company_id' => $this->resolveCompanyId(),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function updateCategory(int $id, array $data): bool
    {
        return $this->updateById($id, $data);
    }

    public function deleteCategory(int $id): bool
    {
        return $this->deleteById($id);
    }
}

