<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\AuditLog;

final class AuditController extends Controller
{
    public function index(): void
    {
        $this->view('reports/logs', [
            'logs' => (new AuditLog())->recent(250),
        ]);
    }
}

