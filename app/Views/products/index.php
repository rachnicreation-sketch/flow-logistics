<section class="page-header">
    <div class="header-left">
        <h2>Catalogue & Inventaire</h2>
        <p>Gérez vos produits, catégories et suivez les alertes de stock.</p>
    </div>
    <div class="header-right">
        <button class="btn btn-outline" onclick="document.getElementById('newCategoryPanel').classList.toggle('hidden')">
            <i class="fa-solid fa-folder-plus"></i> Nouvelle Catégorie
        </button>
        <button class="btn btn-primary" onclick="document.getElementById('newProductPanel').classList.toggle('hidden')">
            <i class="fa-solid fa-plus"></i> Nouveau Produit
        </button>
    </div>
</section>

<div class="split-grid-7-3 mb-6">
    <div class="grid-left">
        <section id="newCategoryPanel" class="panel panel-pad hidden mb-4">
            <h3>Nouvelle Catégorie</h3>
            <form method="post" action="<?= e(url('/products/categories')) ?>" class="grid-form">
                <?= csrf_field() ?>
                <label>Nom<input type="text" name="name" required placeholder="Ex: Électronique"></label>
                <label>Description<textarea name="description" placeholder="Description de la catégorie..."></textarea></label>
                <button class="btn btn-blue" type="submit">Créer la catégorie</button>
            </form>
        </section>

        <section id="newProductPanel" class="panel panel-pad hidden">
            <h3>Ajouter un nouveau produit</h3>
            <form method="post" action="<?= e(url('/products')) ?>" class="grid-form-3">
                <?= csrf_field() ?>
                <label>Nom du produit<input type="text" name="name" required placeholder="Ex: Tablette WM500"></label>
                <label>SKU (Référence)<input type="text" name="sku" required placeholder="SKU-XXX"></label>
                <label>Code-barres<input type="text" name="barcode" placeholder="Auto si vide"></label>
                <label>Catégorie
                    <select name="category_id">
                        <option value="">Sélectionner...</option>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= (int) $c['id'] ?>"><?= e($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Unité
                    <select name="unit">
                        <option value="piece">Pièce</option>
                        <option value="carton">Carton</option>
                        <option value="kg">Kg</option>
                        <option value="litre">Litre</option>
                    </select>
                </label>
                <label>Stock minimum<input type="number" step="0.01" name="min_stock" value="10"></label>
                <label>Prix d'achat (FCFA)<input type="number" step="1" name="purchase_price" placeholder="0"></label>
                <label>Prix de vente (FCFA)<input type="number" step="1" name="sale_price" placeholder="0"></label>
                <div class="full-width mt-4">
                    <button class="btn btn-primary" type="submit">Enregistrer le produit</button>
                </div>
            </form>
        </section>
    </div>
    
    <div class="grid-right">
        <article class="panel h-full">
            <div class="panel-header"><h3><i class="fa-solid fa-triangle-exclamation"></i> Alertes Stock</h3></div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Produit</th><th>Stock</th><th>Min</th></tr></thead>
                    <tbody>
                    <?php if (empty($lowStock)): ?>
                        <tr><td colspan="3" class="empty-row">Aucune alerte.</td></tr>
                    <?php else: ?>
                        <?php foreach (array_slice($lowStock, 0, 5) as $l): ?>
                        <tr>
                            <td><small><?= e($l['sku']) ?></small><br><strong><?= e($l['name']) ?></strong></td>
                            <td><span class="badge badge-danger"><?= e((string) $l['current_stock']) ?></span></td>
                            <td><?= e((string) $l['min_stock']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </article>
    </div>
</div>

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
    <div class="panel-header">
        <h3><i class="fa-solid fa-layer-group"></i> Catalogue complet des produits</h3>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Produit</th><th>Catégorie</th><th>Logistique</th><th>Tarification</th><th>Action</th></tr></thead>
            <tbody>
            <?php foreach ($products as $p): ?>
                <tr>
                    <td>
                        <strong><?= e($p['name']) ?></strong><br>
                        <small class="muted">SKU: <?= e($p['sku']) ?> | EAN: <?= e($p['barcode'] ?? '-') ?></small>
                    </td>
                    <td><span class="badge badge-info"><?= e($p['category_name'] ?? 'Non classé') ?></span></td>
                    <td>
                        <small>Unité: <?= e($p['unit']) ?></small><br>
                        <small>Seuil: <?= e((string) $p['min_stock']) ?></small>
                    </td>
                    <td>
                        <small>Achat: <?= format_currency($p['purchase_price']) ?></small><br>
                        <strong>Vente: <?= format_currency($p['sale_price']) ?></strong>
                    </td>
                    <td>
                        <a class="btn btn-outline btn-sm" href="<?= e(url('/products/' . $p['id'])) ?>">
                            <i class="fa-solid fa-pen"></i> Gérer
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
