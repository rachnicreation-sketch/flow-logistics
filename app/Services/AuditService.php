<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;

final class AuditService
{
    private AuditLog $logs;

    public function __construct()
    {
        $this->logs = new AuditLog();
    }

    public function log(string $action, string $module, ?int $entityId = null, array $meta = []): void
    {
        $this->logs->add($action, $module, $entityId, $meta);
    }
}

