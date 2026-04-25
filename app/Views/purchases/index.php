<section class="page-header">
    <div class="header-left">
        <h2>Achats Fournisseurs</h2>
        <p>Gérez vos approvisionnements, bons d'achat et réceptions de marchandises.</p>
    </div>
    <div class="header-right">
        <button class="btn btn-primary" onclick="document.getElementById('newPurchasePanel').classList.toggle('hidden')">
            <i class="fa-solid fa-cart-plus"></i> Nouveau Bon d'Achat
        </button>
    </div>
</section>

<section id="newPurchasePanel" class="panel panel-pad hidden mb-6">
    <h3>Créer un nouveau bon d'achat</h3>
    <form method="post" action="<?= e(url('/purchases')) ?>" class="grid-form" id="purchaseForm">
        <?= csrf_field() ?>
        <div class="split-grid mb-4">
            <label>Fournisseur
                <select name="supplier_id">
                    <?php foreach ($suppliers as $s): ?>
                        <option value="<?= (int) $s['id'] ?>"><?= e($s['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Référence Interne<input type="text" name="reference" required value="PO-<?= date('YmdHis') ?>"></label>
            <label>Date prévue de réception<input type="date" name="expected_date"></label>
        </div>

        <div class="item-rows" data-module="purchase">
            <div class="item-row mb-2">
                <select name="product_id[]" style="flex: 2;">
                    <?php foreach ($products as $p): ?>
                        <option value="<?= (int) $p['id'] ?>"><?= e($p['name']) ?> (<?= e($p['sku']) ?>)</option>
                    <?php endforeach; ?>
                </select>
                <input type="number" step="1" name="quantity[]" placeholder="Qte" required style="flex: 1;">
                <input type="number" step="1" name="unit_price[]" placeholder="Prix U (FCFA)" required style="flex: 1;">
            </div>
        </div>

        <div class="action-row mt-4">
            <button type="button" class="btn btn-outline add-line"><i class="fa-solid fa-plus"></i> Ajouter un produit</button>
            <button class="btn btn-primary" type="submit">Valider la commande d'achat</button>
        </div>
    </form>
</section>

<section class="panel">
    <div class="panel-header">
        <h3><i class="fa-solid fa-truck-loading"></i> Historique et Réceptions des Achats</h3>
    </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Ref</th><th>Fournisseur</th><th>Montant</th><th>Statut</th><th>Réception</th></tr></thead>
                <tbody>
                <?php foreach ($purchases as $p): ?>
                    <tr>
                        <td><?= e($p['reference']) ?></td>
                        <td><?= e($p['supplier_name']) ?></td>
                        <td><strong><?= format_currency($p['total_amount']) ?></strong></td>
                        <td><span class="badge badge-info"><?= e($p['status']) ?></span></td>
                        <td>
                            <?php if ($p['status'] !== 'received'): ?>
                                <form method="post" action="<?= e(url('/purchases/' . $p['id'] . '/receive')) ?>" class="inline-form">
                                    <?= csrf_field() ?>
                                    <select name="warehouse_id">
                                        <?php foreach ($warehouses as $w): ?>
                                            <option value="<?= (int) $w['id'] ?>"><?= e($w['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button class="btn btn-outline" type="submit">Réceptionner</button>
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
