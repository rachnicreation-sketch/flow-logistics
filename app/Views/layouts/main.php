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
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="app-shell">
    <aside class="sidebar">
        <div class="brand">
            <h1>Flow Logistics</h1>
            <p>Plateforme SCM complete</p>
        </div>

        <nav class="menu">
            <a href="<?= e(url('/dashboard')) ?>" class="<?= layout_is_active('/dashboard', $currentPath) ?>">
                <span class="menu-mark">DB</span>
                <span>Dashboard</span>
            </a>

            <?php if (Auth::can('users.manage')): ?>
            <div class="menu-group">Administration</div>
            <?php endif; ?>

            <?php if (Auth::can('users.manage')): ?>
            <a href="<?= e(url('/users')) ?>" class="<?= layout_is_active('/users', $currentPath) ?>">
                <span class="menu-mark">US</span>
                <span>Utilisateurs</span>
            </a>
            <?php endif; ?>

            <?php if (Auth::can('suppliers.manage') || Auth::can('products.manage') || Auth::can('warehouses.manage') || Auth::can('stocks.manage')): ?>
            <div class="menu-group">Catalogue et stock</div>
            <?php endif; ?>

            <?php if (Auth::can('suppliers.manage')): ?>
            <a href="<?= e(url('/suppliers')) ?>" class="<?= layout_is_active('/suppliers', $currentPath) ?>">
                <span class="menu-mark">FO</span>
                <span>Fournisseurs</span>
            </a>
            <?php endif; ?>

            <?php if (Auth::can('products.manage')): ?>
            <a href="<?= e(url('/products')) ?>" class="<?= layout_is_active('/products', $currentPath) ?>">
                <span class="menu-mark">PR</span>
                <span>Produits</span>
            </a>
            <?php endif; ?>

            <?php if (Auth::can('warehouses.manage')): ?>
            <a href="<?= e(url('/warehouses')) ?>" class="<?= layout_is_active('/warehouses', $currentPath) ?>">
                <span class="menu-mark">WH</span>
                <span>Entrepots</span>
            </a>
            <?php endif; ?>

            <?php if (Auth::can('stocks.manage')): ?>
            <a href="<?= e(url('/stocks')) ?>" class="<?= layout_is_active('/stocks', $currentPath) ?>">
                <span class="menu-mark">ST</span>
                <span>Stocks</span>
            </a>
            <?php endif; ?>

            <?php if (Auth::can('purchases.manage') || Auth::can('orders.manage') || Auth::can('deliveries.manage')): ?>
            <div class="menu-group">Flux commercial</div>
            <?php endif; ?>

            <?php if (Auth::can('purchases.manage')): ?>
            <a href="<?= e(url('/purchases')) ?>" class="<?= layout_is_active('/purchases', $currentPath) ?>">
                <span class="menu-mark">PO</span>
                <span>Achats</span>
            </a>
            <?php endif; ?>

            <?php if (Auth::can('orders.manage')): ?>
            <a href="<?= e(url('/customers')) ?>" class="<?= layout_is_active('/customers', $currentPath) ?>">
                <span class="menu-mark">CL</span>
                <span>Clients</span>
            </a>
            <a href="<?= e(url('/orders')) ?>" class="<?= layout_is_active('/orders', $currentPath) ?>">
                <span class="menu-mark">SO</span>
                <span>Commandes</span>
            </a>
            <?php endif; ?>

            <?php if (Auth::can('deliveries.manage')): ?>
            <a href="<?= e(url('/deliveries')) ?>" class="<?= layout_is_active('/deliveries', $currentPath) ?>">
                <span class="menu-mark">DL</span>
                <span>Livraisons</span>
            </a>
            <?php endif; ?>

            <?php if (Auth::can('deliveries.driver')): ?>
            <a href="<?= e(url('/driver/deliveries')) ?>" class="<?= layout_is_active('/driver', $currentPath) ?>">
                <span class="menu-mark">DR</span>
                <span>Espace chauffeur</span>
            </a>
            <?php endif; ?>

            <?php if (Auth::can('reports.view') || Auth::can('settings.manage') || Auth::can('dashboard.view')): ?>
            <div class="menu-group">Pilotage</div>
            <?php endif; ?>

            <?php if (Auth::can('dashboard.view')): ?>
            <a href="<?= e(url('/messages')) ?>" class="<?= layout_is_active('/messages', $currentPath) ?>">
                <span class="menu-mark">MS</span>
                <span>Messages</span>
            </a>
            <a href="<?= e(url('/notifications')) ?>" class="<?= layout_is_active('/notifications', $currentPath) ?>">
                <span class="menu-mark">NT</span>
                <span>Notifications</span>
            </a>
            <?php if (($user['role_slug'] ?? '') !== 'driver'): ?>
                <a href="<?= e(url('/tickets')) ?>" class="<?= layout_is_active('/tickets', $currentPath) ?>">
                    <span class="menu-mark">TK</span>
                    <span>Ticketing</span>
                </a>
            <?php endif; ?>
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
                <span>Parametres</span>
            </a>
            <?php endif; ?>
        </nav>

        <div class="sidebar-footer">Flow Logistics Â© 2026</div>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <strong><?= e($user['name'] ?? 'Utilisateur') ?></strong>
                <span><?= e($user['role_slug'] ?? '') ?></span>
            </div>
            <form method="post" action="<?= e(url('/logout')) ?>">
                <?= csrf_field() ?>
                <button class="btn btn-outline btn-sm" type="submit">Deconnexion</button>
            </form>
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
