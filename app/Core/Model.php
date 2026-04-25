<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

abstract class Model
{
    protected PDO $db;
    protected string $table = '';

    public function __construct()
    {
        $this->db = Database::connection();
    }

    protected function currentCompanyId(): ?int
    {
        return Auth::companyId();
    }

    protected function isSuperAdmin(): bool
    {
        return (Auth::user()['role_slug'] ?? '') === 'super_admin';
    }

    public function find(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        if (!$this->isSuperAdmin() && $this->hasCompanyColumn()) {
            $sql .= " AND company_id = :company_id";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        if (!$this->isSuperAdmin() && $this->hasCompanyColumn()) {
            $stmt->bindValue(':company_id', $this->currentCompanyId(), PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetch() ?: null;
    }

    public function all(string $order = 'id DESC'): array
    {
        $sql = "SELECT * FROM {$this->table}";
        if (!$this->isSuperAdmin() && $this->hasCompanyColumn()) {
            $sql .= ' WHERE company_id = :company_id';
        }
        $sql .= " ORDER BY {$order}";
        $stmt = $this->db->prepare($sql);
        if (!$this->isSuperAdmin() && $this->hasCompanyColumn()) {
            $stmt->bindValue(':company_id', $this->currentCompanyId(), PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    protected function insert(array $data): int
    {
        $columns = array_keys($data);
        $placeholders = array_map(static fn (string $col): string => ':' . $col, $columns);
        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $stmt = $this->db->prepare($sql);
        foreach ($data as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->execute();
        return (int) $this->db->lastInsertId();
    }

    protected function updateById(int $id, array $data): bool
    {
        $sets = implode(', ', array_map(static fn (string $col): string => "{$col} = :{$col}", array_keys($data)));
        $sql = "UPDATE {$this->table} SET {$sets} WHERE id = :id";
        if (!$this->isSuperAdmin() && $this->hasCompanyColumn()) {
            $sql .= ' AND company_id = :company_id';
        }
        $stmt = $this->db->prepare($sql);
        foreach ($data as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        if (!$this->isSuperAdmin() && $this->hasCompanyColumn()) {
            $stmt->bindValue(':company_id', $this->currentCompanyId(), PDO::PARAM_INT);
        }
        return $stmt->execute();
    }

    protected function deleteById(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        if (!$this->isSuperAdmin() && $this->hasCompanyColumn()) {
            $sql .= ' AND company_id = :company_id';
        }
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        if (!$this->isSuperAdmin() && $this->hasCompanyColumn()) {
            $stmt->bindValue(':company_id', $this->currentCompanyId(), PDO::PARAM_INT);
        }
        return $stmt->execute();
    }

    protected function hasCompanyColumn(): bool
    {
        static $cache = [];
        if (isset($cache[$this->table])) {
            return $cache[$this->table];
        }

        $stmt = $this->db->query("SHOW COLUMNS FROM {$this->table} LIKE 'company_id'");
        $cache[$this->table] = (bool) $stmt->fetch();
        return $cache[$this->table];
    }

    protected function resolveCompanyId(?int $explicit = null): int
    {
        if ($explicit !== null && $explicit > 0) {
            return $explicit;
        }

        if ($this->currentCompanyId() !== null) {
            return (int) $this->currentCompanyId();
        }

        $fallback = (int) $this->db->query('SELECT id FROM companies ORDER BY id ASC LIMIT 1')->fetchColumn();
        if ($fallback <= 0) {
            throw new \RuntimeException('Aucune entreprise disponible.');
        }

        return $fallback;
    }
}

