<section class="page-header">
    <div>
        <h2>Detail stock</h2>
        <p>Ligne stock, rattachements et historique recent.</p>
    </div>
    <div class="action-row">
        <a class="btn btn-outline" href="<?= e(url('/stocks')) ?>">Retour</a>
    </div>
</section>

<section class="split-grid">
    <article class="panel panel-pad">
        <h3>Fiche stock</h3>
        <div class="ticket-meta-grid">
            <div><strong>Produit:</strong> <?= e($stock['product_name']) ?> (<?= e($stock['sku']) ?>)</div>
            <div><strong>Code-barres:</strong> <?= e($stock['barcode'] ?? '-') ?></div>
            <div><strong>Entrepot:</strong> <a href="<?= e(url('/warehouses/' . $stock['warehouse_id'])) ?>"><?= e($stock['warehouse_name']) ?></a></div>
            <div><strong>Emplacement:</strong>
                <?php if (!empty($stock['location_id'])): ?>
                    <a href="<?= e(url('/locations/' . $stock['location_id'])) ?>"><?= e($stock['location_label'] ?? '-') ?></a>
                <?php else: ?>
                    -
                <?php endif; ?>
            </div>
            <div><strong>Quantite:</strong> <?= number_format((float) $stock['quantity'], 2, ',', ' ') ?> <?= e($stock['unit']) ?></div>
            <div><strong>Maj:</strong> <?= e($stock['updated_at']) ?></div>
        </div>
    </article>

    <article class="panel panel-pad">
        <h3>Mouvements recents (global)</h3>
        <p class="muted-text">Les 60 derniers mouvements de stock de l'entreprise.</p>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Date</th><th>Type</th><th>Produit</th><th>Qte</th><th>Ref</th></tr></thead>
                <tbody>
                <?php foreach ($movements as $m): ?>
                <tr>
                    <td><?= e($m['created_at']) ?></td>
                    <td><?= e($m['type']) ?></td>
                    <td><?= e($m['product_name']) ?></td>
                    <td><?= e((string) $m['quantity']) ?></td>
                    <td><?= e(($m['reference_type'] ?? '-') . '#' . ($m['reference_id'] ?? '-')) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>
