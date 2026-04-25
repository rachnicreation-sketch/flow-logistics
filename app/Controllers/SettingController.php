<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Flash;
use App\Models\Setting;
use App\Services\AuditService;

final class SettingController extends Controller
{
    public function index(): void
    {
        $this->view('settings/index', [
            'settings' => (new Setting())->allSettings(),
        ]);
    }

    public function save(): void
    {
        $model = new Setting();
        $pairs = [
            'company_timezone' => (string) $this->input('company_timezone', 'Europe/Paris'),
            'default_currency' => (string) $this->input('default_currency', 'EUR'),
            'smtp_alert_email' => (string) $this->input('smtp_alert_email', ''),
            'stock_alert_threshold' => (string) $this->input('stock_alert_threshold', '1'),
        ];
        foreach ($pairs as $k => $v) {
            $model->upsert($k, $v);
        }
        (new AuditService())->log('UPDATE', 'settings', null, $pairs);
        Flash::set('success', 'Paramètres sauvegardés.');
        $this->redirect('/settings');
    }
}

