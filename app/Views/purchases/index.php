<section class="page-header">
    <div>
        <h2>Achats fournisseurs</h2>
        <p>Bons d'achat, reception marchandises et alimentation automatique du stock.</p>
    </div>
    <div class="action-row">
        <a class="btn btn-outline" href="<?= e(url('/stocks')) ?>">Voir les stocks</a>
    </div>
</section>

<section class="split-grid">
    <article class="panel panel-pad">
        <h3>Nouveau bon d'achat</h3>
        <form method="post" action="<?= e(url('/purchases')) ?>" class="grid-form" id="purchaseForm">
            <?= csrf_field() ?>
            <label>Fournisseur
                <select name="supplier_id">
                    <?php foreach ($suppliers as $s): ?>
                        <option value="<?= (int) $s['id'] ?>"><?= e($s['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Reference<input type="text" name="reference" required value="PO-<?= date('YmdHis') ?>"></label>
            <label>Date prevue<input type="date" name="expected_date"></label>

            <div class="item-rows" data-module="purchase">
                <div class="item-row">
                    <select name="product_id[]">
                        <?php foreach ($products as $p): ?>
                            <option value="<?= (int) $p['id'] ?>"><?= e($p['name']) ?> (<?= e($p['sku']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                    <input type="number" step="0.01" name="quantity[]" placeholder="Qte" required>
                    <input type="number" step="0.01" name="unit_price[]" placeholder="Prix U" required>
                </div>
            </div>

            <div class="action-row">
                <button type="button" class="btn btn-outline add-line">Ajouter ligne produit</button>
                <button class="btn" type="submit">Creer bon d'achat</button>
            </div>
        </form>
    </article>

    <article class="panel">
        <div class="panel-header">
            <h3>Historique achats</h3>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Ref</th><th>Fournisseur</th><th>Montant</th><th>Statut</th><th>Reception</th></tr></thead>
                <tbody>
                <?php foreach ($purchases as $p): ?>
                    <tr>
                        <td><?= e($p['reference']) ?></td>
                        <td><?= e($p['supplier_name']) ?></td>
                        <td><?= number_format((float) $p['total_amount'], 2, ',', ' ') ?></td>
                        <td><span class="badge"><?= e($p['status']) ?></span></td>
                        <td>
                            <?php if ($p['status'] !== 'received'): ?>
                                <form method="post" action="<?= e(url('/purchases/' . $p['id'] . '/receive')) ?>" class="inline-form">
                                    <?= csrf_field() ?>
                                    <select name="warehouse_id">
                                        <?php foreach ($warehouses as $w): ?>
                                            <option value="<?= (int) $w['id'] ?>"><?= e($w['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button class="btn btn-outline" type="submit">Receptionner</button>
                                </form>
                            <?php else: ?>
                                <div class="action-row">
                                    <span class="badge badge-success">Recu</span>
                                    <a class="btn btn-outline btn-sm" href="<?= e(url('/stocks')) ?>">Verifier stock</a>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>
