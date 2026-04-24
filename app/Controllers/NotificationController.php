<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Notification;

final class NotificationController extends Controller
{
    public function index(): void
    {
        $this->view('notifications/index', [
            'notifications' => (new Notification())->forCurrentUser(200),
        ]);
    }
}

