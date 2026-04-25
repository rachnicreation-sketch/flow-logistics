<section class="page-header">
    <div class="header-left">
        <h2>Gestion des Stocks</h2>
        <p>Suivez les entrées, sorties et ajustements de votre inventaire en temps réel.</p>
    </div>
    <div class="header-right">
        <button class="btn btn-primary" onclick="document.getElementById('newMovementPanel').classList.toggle('hidden')">
            <i class="fa-solid fa-right-left"></i> Nouveau Mouvement
        </button>
    </div>
</section>

<section id="newMovementPanel" class="panel panel-pad hidden mb-6">
    <h3>Enregistrer un nouveau mouvement de stock</h3>
    <form method="post" action="<?= e(url('/stocks/move')) ?>" class="grid-form-3">
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
        <label>Type de mouvement
            <select name="type">
                <option value="IN">Entrée</option>
                <option value="OUT">Sortie</option>
                <option value="ADJUST">Ajustement</option>
            </select>
        </label>
        <label>Méthode
            <select name="method">
                <option value="FIFO">FIFO (First In First Out)</option>
                <option value="LIFO">LIFO (Last In First Out)</option>
            </select>
        </label>
        <label>Quantité<input type="number" step="0.01" name="quantity" required placeholder="Ex: 50"></label>
        <label>Coût unitaire (Optionnel)<input type="number" step="0.01" name="unit_cost" placeholder="0.00"></label>
        <label>Motif du mouvement
            <select name="reference_type">
                <option value="manual">Ajustement Manuel</option>
                <option value="inventory">Correction Inventaire</option>
                <option value="damage">Perte / Casse / Rebut</option>
                <option value="return">Retour Client / Fournisseur</option>
            </select>
        </label>
        <label class="full-width">Notes / Justification<textarea name="notes" placeholder="Expliquez la raison de ce mouvement..."></textarea></label>
        <div class="full-width mt-4">
            <button class="btn btn-primary" type="submit">Confirmer le mouvement</button>
        </div>
    </form>
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
