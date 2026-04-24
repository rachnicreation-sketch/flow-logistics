<section class="page-header">
    <div>
        <h2>Rapports et analyse</h2>
        <p>Exports PDF et audit des operations pour piloter votre activite.</p>
    </div>
</section>

<section class="kpi-grid">
    <article class="kpi-card">
        <span class="kpi-label">Lignes stock</span>
        <strong><?= (int) ($stats['stock_rows'] ?? 0) ?></strong>
        <span class="kpi-sub">Instantane exploitable</span>
    </article>
    <article class="kpi-card">
        <span class="kpi-label">Commandes</span>
        <strong><?= (int) ($stats['order_rows'] ?? 0) ?></strong>
        <span class="kpi-sub">Historique commercial</span>
    </article>
    <article class="kpi-card">
        <span class="kpi-label">Livraisons</span>
        <strong><?= (int) ($stats['delivery_rows'] ?? 0) ?></strong>
        <span class="kpi-sub">Suivi transport</span>
    </article>
    <article class="kpi-card success">
        <span class="kpi-label">Conformite</span>
        <strong>OK</strong>
        <span class="kpi-sub">TraÃƒÂ§abilite disponible</span>
    </article>
</section>

<section class="split-grid">
    <article class="panel panel-pad">
        <h3>Exports PDF</h3>
        <p class="muted-text">Generez les rapports officiels pour partager un etat complet de vos operations.</p>
        <div class="action-row">
            <a class="btn" href="<?= e(url('/reports/stocks')) ?>" target="_blank" rel="noopener">Rapport stocks</a>
            <a class="btn btn-outline" href="<?= e(url('/reports/orders')) ?>" target="_blank" rel="noopener">Rapport commandes</a>
            <a class="btn btn-outline" href="<?= e(url('/reports/deliveries')) ?>" target="_blank" rel="noopener">Rapport livraisons</a>
        </div>
    </article>
    <article class="panel panel-pad">
        <h3>Audit et gouvernance</h3>
        <p class="muted-text">Consultez toutes les actions utilisateurs, controles et evenements systeme.</p>
        <div class="action-row">
            <a class="btn" href="<?= e(url('/logs')) ?>">Ouvrir les logs</a>
            <a class="btn btn-outline" href="<?= e(url('/settings')) ?>">Parametres</a>
        </div>
    </article>
</section>
