<section class="page-header">
    <h2>Produits et previsions</h2>
    <p>Catalogue, categories, code-barres, alertes stock et besoins d'approvisionnement.</p>
</section>

<section class="tri-grid">
    <article class="panel panel-pad">
        <h3>Nouvelle categorie</h3>
        <form method="post" action="<?= e(url('/products/categories')) ?>" class="grid-form">
            <?= csrf_field() ?>
            <label>Nom<input type="text" name="name" required></label>
            <label>Description<textarea name="description"></textarea></label>
            <button class="btn btn-blue" type="submit">Créer categorie</button>
        </form>
    </article>

    <article class="panel panel-pad">
        <h3>Nouveau produit</h3>
        <form method="post" action="<?= e(url('/products')) ?>" class="grid-form">
            <?= csrf_field() ?>
            <label>Nom<input type="text" name="name" required></label>
            <label>SKU<input type="text" name="sku" required></label>
            <label>Code-barres (laisser vide = auto)
                <input type="text" name="barcode">
            </label>
            <label>Categorie
                <select name="category_id">
                    <option value="">-</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= (int) $c['id'] ?>"><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Unite
                <select name="unit">
                    <option value="piece">Piece</option>
                    <option value="carton">Carton</option>
                    <option value="kg">Kg</option>
                    <option value="litre">Litre</option>
                </select>
            </label>
            <label>Prix achat<input type="number" step="0.01" name="purchase_price"></label>
            <label>Prix vente<input type="number" step="0.01" name="sale_price"></label>
            <label>Stock minimum<input type="number" step="0.01" name="min_stock"></label>
            <button class="btn btn-yellow" type="submit">Créer produit</button>
        </form>
    </article>

    <article class="panel">
        <div class="panel-header"><h3>Alertes stock faible</h3></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>SKU</th><th>Produit</th><th>Stock</th><th>Min</th></tr></thead>
                <tbody>
                <?php if (empty($lowStock)): ?>
                    <tr><td colspan="4" class="empty-row">Aucune alerte.</td></tr>
                <?php else: ?>
                    <?php foreach ($lowStock as $l): ?>
                    <tr>
                        <td><?= e($l['sku']) ?></td>
                        <td><?= e($l['name']) ?></td>
                        <td><span class="badge badge-danger"><?= e((string) $l['current_stock']) ?></span></td>
                        <td><?= e((string) $l['min_stock']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>

<section class="panel">
    <div class="panel-header"><h3>Prevision de besoins</h3></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>SKU</th><th>Produit</th><th>Stock actuel</th><th>Stock min</th><th>Ecart</th></tr></thead>
            <tbody>
            <?php foreach (array_slice($forecast, 0, 20) as $f): ?>
                <?php $gap = (float) $f['min_stock'] - (float) $f['current_stock']; ?>
                <tr>
                    <td><?= e($f['sku']) ?></td>
                    <td><?= e($f['name']) ?></td>
                    <td><?= e((string) $f['current_stock']) ?></td>
                    <td><?= e((string) $f['min_stock']) ?></td>
                    <td><?= number_format($gap, 2, ',', ' ') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="panel">
    <div class="panel-header"><h3>Catalogue produit</h3></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>SKU</th><th>Nom</th><th>Categorie</th><th>Code-barres</th><th>Unite</th><th>Prix Achat</th><th>Prix Vente</th><th>Min</th><th>Action</th></tr></thead>
            <tbody>
            <?php foreach ($products as $p): ?>
                <tr>
                    <td><?= e($p['sku']) ?></td>
                    <td><?= e($p['name']) ?></td>
                    <td><?= e($p['category_name'] ?? '-') ?></td>
                    <td><?= e($p['barcode'] ?? '-') ?></td>
                    <td><?= e($p['unit']) ?></td>
                    <td><?= number_format((float) $p['purchase_price'], 2, ',', ' ') ?></td>
                    <td><?= number_format((float) $p['sale_price'], 2, ',', ' ') ?></td>
                    <td><?= e((string) $p['min_stock']) ?></td>
                    <td><a class="btn btn-blue btn-sm" href="<?= e(url('/products/' . $p['id'])) ?>">Detail / Modifier</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
