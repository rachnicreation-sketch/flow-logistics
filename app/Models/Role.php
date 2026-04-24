<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Role extends Model
{
    protected string $table = 'roles';

    public function allRoles(): array
    {
        return $this->db->query('SELECT * FROM roles ORDER BY id ASC')->fetchAll();
    }
}

