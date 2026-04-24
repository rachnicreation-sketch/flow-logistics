<section class="page-header">
    <div>
        <h2>Emplacement: <?= e($location['label']) ?></h2>
        <p>Detail emplacement, zone et quantites stockees.</p>
    </div>
    <div class="action-row">
        <a class="btn btn-outline" href="<?= e(url('/warehouses/' . $location['warehouse_id'])) ?>">Retour entrepot</a>
    </div>
</section>

<section class="panel panel-pad">
    <div class="ticket-meta-grid">
        <div><strong>Entrepot:</strong> <?= e($location['warehouse_name']) ?> (<?= e($location['warehouse_code']) ?>)</div>
        <div><strong>Zone:</strong> <?= e($location['zone_name']) ?></div>
        <div><strong>Capacite:</strong> <?= e((string) ($location['capacity'] ?? '-')) ?></div>
        <div><strong>Stock cumule:</strong> <?= number_format((float) ($location['stock_quantity'] ?? 0), 2, ',', ' ') ?></div>
        <div><strong>Cree le:</strong> <?= e($location['created_at']) ?></div>
    </div>
</section>
