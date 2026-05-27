<section class="page-header">
    <div>
        <h2>Rapports & Statistiques ERP</h2>
        <p>Analyse des performances logistiques, financières et opérationnelles.</p>
    </div>
</section>

<!-- KPIs financiers -->
<section class="kpi-grid" style="grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));">
    <article class="kpi-card success">
        <span class="kpi-label">CA total encaissé</span>
        <strong><?= number_format((float)($stats['total_revenue'] ?? 0), 2, ',', ' ') ?> €</strong>
        <span class="kpi-sub">Factures payées</span>
    </article>
    <article class="kpi-card warning">
        <span class="kpi-label">Créances impayées</span>
        <strong><?= number_format((float)($stats['unpaid_invoices'] ?? 0), 2, ',', ' ') ?> €</strong>
        <span class="kpi-sub">À recouvrer</span>
    </article>
    <article class="kpi-card">
        <span class="kpi-label">Total dépenses</span>
        <strong><?= number_format((float)($stats['total_expenses'] ?? 0), 2, ',', ' ') ?> €</strong>
        <span class="kpi-sub">Opérationnel</span>
    </article>
    <article class="kpi-card">
        <span class="kpi-label">Commandes</span>
        <strong><?= (int)($stats['order_rows'] ?? 0) ?></strong>
        <span class="kpi-sub">Total historique</span>
    </article>
    <article class="kpi-card">
        <span class="kpi-label">Livraisons</span>
        <strong><?= (int)($stats['delivery_rows'] ?? 0) ?></strong>
        <span class="kpi-sub">Total réalisées</span>
    </article>
    <article class="kpi-card">
        <span class="kpi-label">Lignes stock</span>
        <strong><?= (int)($stats['stock_rows'] ?? 0) ?></strong>
        <span class="kpi-sub">Références actives</span>
    </article>
    <article class="kpi-card">
        <span class="kpi-label">Retours traités</span>
        <strong><?= (int)($stats['return_rows'] ?? 0) ?></strong>
        <span class="kpi-sub">Reverse logistics</span>
    </article>
    <article class="kpi-card success">
        <span class="kpi-label">Conformité</span>
        <strong>OK</strong>
        <span class="kpi-sub">Traçabilité disponible</span>
    </article>
</section>

<!-- Exports -->
<section class="split-grid">
    <article class="panel panel-pad">
        <h3 style="margin-bottom: 10px;">📄 Exports opérationnels (PDF / CSV)</h3>
        <p class="muted-text" style="margin-bottom: 15px;">Générez les rapports officiels pour partager un état complet de vos opérations.</p>
        <div class="action-row" style="display: flex; flex-wrap: wrap; gap: 10px;">
            <a class="btn btn-outline" href="<?= e(url('/reports/stocks')) ?>" target="_blank" rel="noopener">
                <i class="fa-solid fa-boxes-stacking"></i> Stocks (PDF)
            </a>
            <a class="btn btn-outline" href="<?= e(url('/reports/stocks/csv')) ?>">
                <i class="fa-solid fa-file-csv"></i> Stocks (CSV)
            </a>
            <a class="btn btn-outline" href="<?= e(url('/reports/orders')) ?>" target="_blank" rel="noopener">
                <i class="fa-solid fa-file-invoice"></i> Commandes (PDF)
            </a>
            <a class="btn btn-outline" href="<?= e(url('/reports/deliveries')) ?>" target="_blank" rel="noopener">
                <i class="fa-solid fa-truck"></i> Livraisons (PDF)
            </a>
        </div>
    </article>

    <article class="panel panel-pad">
        <h3 style="margin-bottom: 10px;">🔍 Audit & Gouvernance</h3>
        <p class="muted-text" style="margin-bottom: 15px;">Consultez toutes les actions utilisateurs, contrôles et événements système.</p>
        <div class="action-row" style="display: flex; flex-wrap: wrap; gap: 10px;">
            <a class="btn btn-outline" href="<?= e(url('/logs')) ?>">
                <i class="fa-solid fa-scroll"></i> Journal d'activité
            </a>
            <a class="btn btn-outline" href="<?= e(url('/settings')) ?>">
                <i class="fa-solid fa-cog"></i> Paramètres
            </a>
        </div>
    </article>
</section>

<!-- Liens vers modules -->
<section class="split-grid">
    <article class="panel panel-pad">
        <h3 style="margin-bottom: 15px;">📊 Modules financiers</h3>
        <div style="display: flex; flex-direction: column; gap: 10px;">
            <a href="<?= e(url('/invoices')) ?>" class="btn btn-outline" style="text-align: left;">
                <i class="fa-solid fa-file-invoice-dollar" style="width: 20px;"></i> Factures &amp; Paiements
            </a>
            <a href="<?= e(url('/expenses')) ?>" class="btn btn-outline" style="text-align: left;">
                <i class="fa-solid fa-receipt" style="width: 20px;"></i> Dépenses opérationnelles
            </a>
            <a href="<?= e(url('/customs')) ?>" class="btn btn-outline" style="text-align: left;">
                <i class="fa-solid fa-globe" style="width: 20px;"></i> Déclarations douanières
            </a>
            <a href="<?= e(url('/returns')) ?>" class="btn btn-outline" style="text-align: left;">
                <i class="fa-solid fa-rotate-left" style="width: 20px;"></i> Retours logistiques
            </a>
        </div>
    </article>
    <article class="panel panel-pad">
        <h3 style="margin-bottom: 15px;">🚛 Modules transport &amp; flotte</h3>
        <div style="display: flex; flex-direction: column; gap: 10px;">
            <a href="<?= e(url('/deliveries')) ?>" class="btn btn-outline" style="text-align: left;">
                <i class="fa-solid fa-truck" style="width: 20px;"></i> Livraisons &amp; Planification
            </a>
            <a href="<?= e(url('/parcels')) ?>" class="btn btn-outline" style="text-align: left;">
                <i class="fa-solid fa-box" style="width: 20px;"></i> Colis &amp; Expéditions
            </a>
            <a href="<?= e(url('/drivers')) ?>" class="btn btn-outline" style="text-align: left;">
                <i class="fa-solid fa-id-card" style="width: 20px;"></i> Chauffeurs (RH)
            </a>
            <a href="<?= e(url('/maintenances')) ?>" class="btn btn-outline" style="text-align: left;">
                <i class="fa-solid fa-wrench" style="width: 20px;"></i> Maintenance flotte
            </a>
        </div>
    </article>
</section>
