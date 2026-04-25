<section class="page-header">
    <div>
        <h2>Produit: <?= e($product['name']) ?></h2>
        <p>Detail produit, edition, code-barres et stock par entrepot.</p>
    </div>
    <div class="action-row">
        <a class="btn btn-outline" href="<?= e(url('/products')) ?>">Retour</a>
    </div>
</section>

<section class="split-grid">
    <article class="panel panel-pad">
        <h3>Edition produit</h3>
        <form method="post" action="<?= e(url('/products/' . $product['id'] . '/update')) ?>" class="grid-form">
            <?= csrf_field() ?>
            <label>Nom<input type="text" name="name" value="<?= e($product['name']) ?>" required></label>
            <label>SKU<input type="text" name="sku" value="<?= e($product['sku']) ?>" required></label>
            <label>Code-barres
                <input type="text" name="barcode" value="<?= e($product['barcode'] ?? '') ?>">
            </label>
            <label>Categorie
                <select name="category_id">
                    <option value="">-</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= (int) $c['id'] ?>" <?= (int) ($product['category_id'] ?? 0) === (int) $c['id'] ? 'selected' : '' ?>>
                            <?= e($c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Unite
                <select name="unit">
                    <?php foreach (['piece', 'carton', 'kg', 'litre'] as $unit): ?>
                        <option value="<?= e($unit) ?>" <?= ($product['unit'] ?? '') === $unit ? 'selected' : '' ?>><?= e($unit) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Prix achat<input type="number" step="0.01" name="purchase_price" value="<?= e((string) $product['purchase_price']) ?>"></label>
            <label>Prix vente<input type="number" step="0.01" name="sale_price" value="<?= e((string) $product['sale_price']) ?>"></label>
            <label>Stock minimum<input type="number" step="0.01" name="min_stock" value="<?= e((string) $product['min_stock']) ?>"></label>
            <button class="btn" type="submit">Enregistrer</button>
        </form>
    </article>

    <article class="panel panel-pad">
        <h3>Fiche detail</h3>
        <div class="ticket-meta-grid">
            <div><strong>Categorie:</strong> <?= e($product['category_name'] ?? '-') ?></div>
            <div><strong>Stock global:</strong> <?= number_format((float) ($product['current_stock'] ?? 0), 2, ',', ' ') ?></div>
            <div><strong>Code-barres:</strong> <?= e($product['barcode'] ?? '-') ?></div>
            <div><strong>Statut:</strong> <?= e($product['status'] ?? '-') ?></div>
        </div>
        <div class="action-row">
            <form method="post" action="<?= e(url('/products/' . $product['id'] . '/barcode/delete')) ?>">
                <?= csrf_field() ?>
                <button class="btn btn-outline" type="submit">Supprimer code-barres</button>
            </form>
            <?php if (!empty($canDelete)): ?>
            <form method="post" action="<?= e(url('/products/' . $product['id'] . '/delete')) ?>">
                <?= csrf_field() ?>
                <button class="btn btn-danger" type="submit">Supprimer produit</button>
            </form>
            <?php endif; ?>
        </div>
    </article>
</section>

<section class="panel">
    <div class="panel-header">
        <h3>Stock par entrepot</h3>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Entrepôt</th><th>Emplacement</th><th>Quantite</th><th>Action</th></tr></thead>
            <tbody>
            <?php if (empty($stocks)): ?>
                <tr><td colspan="4" class="empty-row">Aucune ligne de stock pour ce produit.</td></tr>
            <?php else: ?>
                <?php foreach ($stocks as $s): ?>
                <tr>
                    <td><?= e($s['warehouse_name']) ?></td>
                    <td><?= e($s['location_label'] ?? '-') ?></td>
                    <td><?= number_format((float) $s['quantity'], 2, ',', ' ') ?></td>
                    <td><a class="btn btn-outline btn-sm" href="<?= e(url('/stocks/' . $s['stock_id'])) ?>">Voir stock</a></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
