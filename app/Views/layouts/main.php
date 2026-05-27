<?php

use App\Core\Auth;

$user = Auth::user();
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
if ($scriptDir !== '' && str_starts_with($currentPath, $scriptDir)) {
    $currentPath = substr($currentPath, strlen($scriptDir));
}
$currentPath = '/' . trim($currentPath, '/');
$currentPath = $currentPath === '/' ? '/' : rtrim($currentPath, '/');

if (!function_exists('layout_is_active')) {
    function layout_is_active(string $path, string $current): string
    {
        return ($current === $path || str_starts_with($current, $path . '/')) ? 'active' : '';
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(config('app.name')) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="app-shell">
    <aside class="sidebar">
        <div class="brand">
            <h1>Flow Logistics</h1>
            <p>Plateforme SCM complète</p>
        </div>

        <nav class="menu">
            <a href="<?= e(url('/dashboard')) ?>" class="<?= layout_is_active('/dashboard', $currentPath) ?>">
                <span class="menu-mark">DB</span>
                <span><?= __('dashboard') ?></span>
            </a>

            <?php if (Auth::can('users.manage')): ?>
            <div class="menu-group">Administration</div>
            <?php endif; ?>

            <?php if (Auth::can('users.manage')): ?>
            <a href="<?= e(url('/users')) ?>" class="<?= layout_is_active('/users', $currentPath) ?>">
                <span class="menu-mark">US</span>
                <span><?= __('users') ?></span>
            </a>
            <?php endif; ?>

            <?php if (Auth::can('suppliers.manage') || Auth::can('products.manage') || Auth::can('warehouses.manage') || Auth::can('stocks.manage')): ?>
            <div class="menu-group">Catalogue et stock</div>
            <?php endif; ?>

            <?php if (Auth::can('suppliers.manage')): ?>
            <a href="<?= e(url('/suppliers')) ?>" class="<?= layout_is_active('/suppliers', $currentPath) ?>">
                <span class="menu-mark">FO</span>
                <span><?= __('suppliers') ?></span>
            </a>
            <?php endif; ?>

            <?php if (Auth::can('products.manage')): ?>
            <a href="<?= e(url('/products')) ?>" class="<?= layout_is_active('/products', $currentPath) ?>">
                <span class="menu-mark">PR</span>
                <span><?= __('products') ?></span>
            </a>
            <a href="<?= e(url('/categories')) ?>" class="<?= layout_is_active('/categories', $currentPath) ?>">
                <span class="menu-mark">CA</span>
                <span><?= __('categories') ?></span>
            </a>
            <?php endif; ?>

            <?php if (Auth::can('warehouses.manage')): ?>
            <a href="<?= e(url('/warehouses')) ?>" class="<?= layout_is_active('/warehouses', $currentPath) ?>">
                <span class="menu-mark">WH</span>
                <span><?= __('warehouses') ?></span>
            </a>
            <?php endif; ?>

            <?php if (Auth::can('stocks.manage')): ?>
            <a href="<?= e(url('/stocks')) ?>" class="<?= layout_is_active('/stocks', $currentPath) ?>">
                <span class="menu-mark">ST</span>
                <span><?= __('stocks') ?></span>
            </a>
            <a href="<?= e(url('/inventories')) ?>" class="<?= layout_is_active('/inventories', $currentPath) ?>">
                <span class="menu-mark">IV</span>
                <span><?= __('inventories') ?></span>
            </a>
            <?php endif; ?>

            <?php if (Auth::can('purchases.manage') || Auth::can('orders.manage') || Auth::can('deliveries.manage')): ?>
            <div class="menu-group">Flux commercial</div>
            <?php endif; ?>

            <?php if (Auth::can('purchases.manage')): ?>
            <a href="<?= e(url('/purchases')) ?>" class="<?= layout_is_active('/purchases', $currentPath) ?>">
                <span class="menu-mark">PO</span>
                <span><?= __('purchases') ?></span>
            </a>
            <?php endif; ?>

            <?php if (Auth::can('orders.manage')): ?>
            <a href="<?= e(url('/customers')) ?>" class="<?= layout_is_active('/customers', $currentPath) ?>">
                <span class="menu-mark">CL</span>
                <span><?= __('customers') ?></span>
            </a>
            <a href="<?= e(url('/orders')) ?>" class="<?= layout_is_active('/orders', $currentPath) ?>">
                <span class="menu-mark">SO</span>
                <span><?= __('orders') ?></span>
            </a>
            <a href="<?= e(url('/invoices')) ?>" class="<?= layout_is_active('/invoices', $currentPath) ?>">
                <span class="menu-mark">FC</span>
                <span>Factures &amp; Paiements</span>
            </a>
            <a href="<?= e(url('/returns')) ?>" class="<?= layout_is_active('/returns', $currentPath) ?>">
                <span class="menu-mark">RT</span>
                <span>Retours clients</span>
            </a>
            <?php endif; ?>

            <?php if (Auth::can('deliveries.manage')): ?>
            <a href="<?= e(url('/vehicles')) ?>" class="<?= layout_is_active('/vehicles', $currentPath) ?>">
                <span class="menu-mark">VH</span>
                <span><?= __('vehicles') ?></span>
            </a>
            <a href="<?= e(url('/drivers')) ?>" class="<?= layout_is_active('/drivers', $currentPath) ?>">
                <span class="menu-mark">CH</span>
                <span>Chauffeurs (RH)</span>
            </a>
            <a href="<?= e(url('/maintenances')) ?>" class="<?= layout_is_active('/maintenances', $currentPath) ?>">
                <span class="menu-mark">MT</span>
                <span>Maintenance Flotte</span>
            </a>
            <a href="<?= e(url('/deliveries')) ?>" class="<?= layout_is_active('/deliveries', $currentPath) ?>">
                <span class="menu-mark">DL</span>
                <span><?= __('deliveries') ?></span>
            </a>
            <a href="<?= e(url('/parcels')) ?>" class="<?= layout_is_active('/parcels', $currentPath) ?>">
                <span class="menu-mark">BX</span>
                <span>Colis & Expéditions</span>
            </a>
            <a href="<?= e(url('/deliveries/planning')) ?>" class="<?= layout_is_active('/deliveries/planning', $currentPath) ?>">
                <span class="menu-mark">PL</span>
                <span><?= __('planning') ?></span>
            </a>
            <?php endif; ?>

            <?php if (Auth::can('deliveries.driver')): ?>
            <a href="<?= e(url('/driver/deliveries')) ?>" class="<?= layout_is_active('/driver', $currentPath) ?>">
                <span class="menu-mark">DR</span>
                <span>Espace chauffeur</span>
            </a>
            <?php endif; ?>

            <?php if (Auth::can('reports.view')): ?>
            <div class="menu-group">Finances &amp; Douanes</div>
            <a href="<?= e(url('/expenses')) ?>" class="<?= layout_is_active('/expenses', $currentPath) ?>">
                <span class="menu-mark">DP</span>
                <span>Dépenses</span>
            </a>
            <a href="<?= e(url('/customs')) ?>" class="<?= layout_is_active('/customs', $currentPath) ?>">
                <span class="menu-mark">DZ</span>
                <span>Douanes</span>
            </a>
            <?php endif; ?>

            <?php if (Auth::can('reports.view') || Auth::can('settings.manage') || Auth::can('dashboard.view') || Auth::can('messages.manage') || Auth::can('tickets.manage')): ?>
            <div class="menu-group">Pilotage</div>
            <?php endif; ?>



            <?php if (Auth::can('tickets.manage')): ?>
            <a href="<?= e(url('/tickets')) ?>" class="<?= layout_is_active('/tickets', $currentPath) ?>">
                <span class="menu-mark">TK</span>
                <span>Ticketing</span>
            </a>
            <?php endif; ?>

            <?php if (Auth::can('reports.view')): ?>
            <a href="<?= e(url('/reports')) ?>" class="<?= layout_is_active('/reports', $currentPath) ?>">
                <span class="menu-mark">RP</span>
                <span>Rapports</span>
            </a>
            <a href="<?= e(url('/logs')) ?>" class="<?= layout_is_active('/logs', $currentPath) ?>">
                <span class="menu-mark">LG</span>
                <span>Logs</span>
            </a>
            <?php endif; ?>

            <?php if (Auth::can('settings.manage')): ?>
            <a href="<?= e(url('/settings')) ?>" class="<?= layout_is_active('/settings', $currentPath) ?>">
                <span class="menu-mark">ST</span>
                <span>Paramètres</span>
            </a>
            <?php endif; ?>
        </nav>

        <div class="sidebar-footer">Flow Logistics © 2026</div>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <button id="sidebarToggle" class="hamburger-btn" aria-label="Menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
                <strong><?= e($user['name'] ?? 'Utilisateur') ?></strong>
                <span><?= e($user['role_slug'] ?? '') ?></span>
            </div>
            <div class="topbar-right">
                <?php if (Auth::can('messages.manage')): ?>
                    <a href="<?= e(url('/messages')) ?>" class="topbar-icon-btn <?= layout_is_active('/messages', $currentPath) ?>" title="Messages">
                        <span class="icon-mark"><i class="fa-solid fa-envelope"></i></span>
                    </a>
                <?php endif; ?>
                <?php if (Auth::can('dashboard.view')): ?>
                    <a href="<?= e(url('/notifications')) ?>" class="topbar-icon-btn <?= layout_is_active('/notifications', $currentPath) ?>" title="Notifications">
                        <span class="icon-mark"><i class="fa-solid fa-bell"></i></span>
                    </a>
                <?php endif; ?>
                <button id="themeToggle" class="topbar-icon-btn" title="Changer le mode">
                    <span class="icon-mark theme-icon"><i class="fa-solid fa-sun"></i></span>
                </button>
                <form method="post" action="<?= e(url('/logout')) ?>">
                    <?= csrf_field() ?>
                    <button class="btn btn-outline btn-sm" type="submit">Déconnexion</button>
                </form>
            </div>
        </header>

        <div class="page-body">
            <?php if (!empty($flash['success'])): ?>
                <?php foreach ($flash['success'] as $message): ?>
                    <div class="alert alert-success"><?= e($message) ?></div>
                <?php endforeach; ?>
            <?php endif; ?>
            <?php if (!empty($flash['error'])): ?>
                <?php foreach ($flash['error'] as $message): ?>
                    <div class="alert alert-danger"><?= e($message) ?></div>
                <?php endforeach; ?>
            <?php endif; ?>

            <?= $content ?>
        </div>
    </main>
</div>
<script src="<?= e(asset('js/app.js')) ?>"></script>
<script src="<?= e(asset('js/charts.js')) ?>"></script>
</body>
</html>
