<section class="page-header">
    <div>
        <h2>Entrepot: <?= e($warehouse['name']) ?></h2>
        <p>Vue detaillee de l'entrepot, des zones et des emplacements.</p>
    </div>
    <div class="action-row">
        <a class="btn btn-outline" href="<?= e(url('/warehouses')) ?>">Retour</a>
    </div>
</section>

<section class="kpi-grid">
    <article class="kpi-card">
        <span class="kpi-label">Code</span>
        <strong><?= e($warehouse['code']) ?></strong>
        <span class="kpi-sub">Identification entrepot</span>
    </article>
    <article class="kpi-card">
        <span class="kpi-label">Zones</span>
        <strong><?= (int) ($warehouse['zone_count'] ?? 0) ?></strong>
        <span class="kpi-sub">Total zones actives</span>
    </article>
    <article class="kpi-card">
        <span class="kpi-label">Emplacements</span>
        <strong><?= (int) ($warehouse['location_count'] ?? 0) ?></strong>
        <span class="kpi-sub">Capacite de stockage</span>
    </article>
    <article class="kpi-card">
        <span class="kpi-label">Stock total</span>
        <strong><?= number_format((float) ($warehouse['total_stock'] ?? 0), 2, ',', ' ') ?></strong>
        <span class="kpi-sub">Quantite globale</span>
    </article>
</section>

<section class="panel">
    <div class="panel-header"><h3>Zones et emplacements</h3></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Zone</th><th>Emplacement</th><th>Capacite</th><th>Detail</th></tr></thead>
            <tbody>
            <?php if (empty($zones)): ?>
                <tr><td colspan="4" class="empty-row">Aucune zone definie.</td></tr>
            <?php else: ?>
                <?php foreach ($zones as $z): ?>
                <tr>
                    <td><?= e($z['zone_name']) ?></td>
                    <td><?= e($z['label'] ?? '-') ?></td>
                    <td><?= e((string) ($z['capacity'] ?? '-')) ?></td>
                    <td>
                        <?php if (!empty($z['location_id'])): ?>
                            <a class="btn btn-outline btn-sm" href="<?= e(url('/locations/' . $z['location_id'])) ?>">Voir</a>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
