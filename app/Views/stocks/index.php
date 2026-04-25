<section class="page-header">
    <h2>Gestion des stocks</h2>
    <p>Entrees, sorties, inventaire et recapitulatifs lisibles par produit/emplacement.</p>
</section>

<section class="split-grid">
    <article class="panel panel-pad">
        <h3>Nouveau mouvement</h3>
        <form method="post" action="<?= e(url('/stocks/move')) ?>" class="grid-form">
            <?= csrf_field() ?>
            <label>Produit
                <select name="product_id">
                    <?php foreach ($products as $p): ?>
                        <option value="<?= (int) $p['id'] ?>"><?= e($p['name']) ?> (<?= e($p['sku']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Entrepôt
                <select name="warehouse_id">
                    <?php foreach ($warehouses as $w): ?>
                        <option value="<?= (int) $w['id'] ?>"><?= e($w['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Type
                <select name="type">
                    <option value="IN">Entree</option>
                    <option value="OUT">Sortie</option>
                    <option value="ADJUST">Ajustement</option>
                </select>
            </label>
            <label>Methode
                <select name="method">
                    <option value="FIFO">FIFO</option>
                    <option value="LIFO">LIFO</option>
                </select>
            </label>
            <label>Quantite<input type="number" step="0.01" name="quantity" required></label>
            <label>Cout unitaire<input type="number" step="0.01" name="unit_cost"></label>
            <label>Reference type<input type="text" name="reference_type" placeholder="manual/order/purchase"></label>
            <label>Reference ID<input type="number" name="reference_id"></label>
            <label>Notes<textarea name="notes"></textarea></label>
            <button class="btn" type="submit">Enregistrer mouvement</button>
        </form>
    </article>

    <article class="panel">
        <div class="panel-header"><h3>Alertes et previsions</h3></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Produit</th><th>Stock</th><th>Min</th><th>Alerte</th></tr></thead>
                <tbody>
                <?php if (empty($lowStock)): ?>
                    <tr><td colspan="4" class="empty-row">Aucune alerte de stock.</td></tr>
                <?php else: ?>
                    <?php foreach ($lowStock as $l): ?>
                    <tr>
                        <td><?= e($l['name']) ?></td>
                        <td><?= e((string) $l['current_stock']) ?></td>
                        <td><?= e((string) $l['min_stock']) ?></td>
                        <td><span class="badge badge-danger">Faible</span></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="table-wrap" style="margin-top:10px;">
            <table>
                <thead><tr><th>Produit</th><th>Stock actuel</th><th>Min</th><th>Ecart</th></tr></thead>
                <tbody>
                <?php foreach (array_slice($forecast, 0, 10) as $f): ?>
                    <?php $gap = (float) $f['min_stock'] - (float) $f['current_stock']; ?>
                    <tr>
                        <td><?= e($f['name']) ?></td>
                        <td><?= e((string) $f['current_stock']) ?></td>
                        <td><?= e((string) $f['min_stock']) ?></td>
                        <td><?= number_format($gap, 2, ',', ' ') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>

<section class="panel">
    <div class="panel-header"><h3>Stock courant par emplacement</h3></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Produit</th><th>SKU</th><th>Entrepôt</th><th>Emplacement</th><th>Quantite</th><th>Detail</th></tr></thead>
            <tbody>
            <?php foreach ($summary as $row): ?>
                <tr>
                    <td><?= e($row['product_name']) ?></td>
                    <td><?= e($row['sku']) ?></td>
                    <td><a href="<?= e(url('/warehouses/' . $row['warehouse_id'])) ?>"><?= e($row['warehouse_name']) ?></a></td>
                    <td>
                        <?php if (!empty($row['location_id'])): ?>
                            <a href="<?= e(url('/locations/' . $row['location_id'])) ?>"><?= e($row['location_label'] ?? '-') ?></a>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td><?= number_format((float) $row['quantity'], 2, ',', ' ') ?></td>
                    <td><a class="btn btn-outline btn-sm" href="<?= e(url('/stocks/' . $row['id'])) ?>">Voir</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="panel">
    <div class="panel-header"><h3>Historique des mouvements</h3></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Date</th><th>Type</th><th>Produit</th><th>Qte</th><th>Methode</th><th>Reference</th><th>Operateur</th></tr></thead>
            <tbody>
            <?php foreach ($movements as $m): ?>
                <tr>
                    <td><?= e($m['created_at']) ?></td>
                    <td><?= e($m['type']) ?></td>
                    <td><?= e($m['product_name']) ?></td>
                    <td><?= e((string) $m['quantity']) ?></td>
                    <td><?= e($m['method']) ?></td>
                    <td><?= e(($m['reference_type'] ?? '-') . '#' . ($m['reference_id'] ?? '-')) ?></td>
                    <td><?= e($m['user_name'] ?? '-') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
