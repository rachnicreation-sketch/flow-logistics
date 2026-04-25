<section class="page-header">
    <div class="header-left">
        <h2>Commandes Clients</h2>
        <p>Gérez vos ventes, factures et préparez les expéditions.</p>
    </div>
    <div class="header-right">
        <button class="btn btn-primary" onclick="document.getElementById('newOrderPanel').classList.toggle('hidden')">
            <i class="fa-solid fa-file-invoice-dollar"></i> Nouvelle Commande
        </button>
    </div>
</section>

<section id="newOrderPanel" class="panel panel-pad hidden mb-6">
    <h3>Créer une nouvelle commande client</h3>
    <form method="post" action="<?= e(url('/orders')) ?>" class="grid-form">
        <?= csrf_field() ?>
        <div class="split-grid mb-4">
            <label>Client
                <select name="customer_id">
                    <?php foreach ($customers as $c): ?>
                        <option value="<?= (int) $c['id'] ?>"><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Référence Commande<input type="text" name="reference" value="SO-<?= date('YmdHis') ?>" required></label>
            <label>Adresse de livraison<input type="text" name="delivery_address" placeholder="Destination finale..."></label>
        </div>

        <div class="item-rows" data-module="order">
            <div class="item-row mb-2">
                <select name="product_id[]" style="flex: 2;">
                    <?php foreach ($products as $p): ?>
                        <option value="<?= (int) $p['id'] ?>"><?= e($p['name']) ?> (<?= e($p['sku']) ?>)</option>
                    <?php endforeach; ?>
                </select>
                <input type="number" step="1" name="quantity[]" placeholder="Qte" required style="flex: 1;">
                <input type="number" step="1" name="unit_price[]" placeholder="Prix Vente (FCFA)" required style="flex: 1;">
            </div>
        </div>

        <div class="action-row mt-4">
            <button type="button" class="btn btn-outline add-line"><i class="fa-solid fa-plus"></i> Ajouter une ligne</button>
            <button class="btn btn-primary" type="submit">Enregistrer la commande</button>
        </div>
    </form>
</section>

<section class="panel">
    <div class="panel-header">
        <h3><i class="fa-solid fa-list-check"></i> Suivi du cycle des Commandes</h3>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Reference</th><th>Client</th><th>Statut</th><th>Facture</th><th>Montant</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($orders as $o): ?>
                <tr>
                    <td><?= e($o['reference']) ?></td>
                    <td><?= e($o['customer_name']) ?></td>
                    <td><span class="badge"><?= e($o['status']) ?></span></td>
                    <td><?= e($o['invoice_number']) ?></td>
                    <td><strong><?= format_currency($o['total_amount']) ?></strong></td>
                    <td class="action-grid">
                        <a class="btn btn-outline" href="<?= e(url('/orders/' . $o['id'] . '/invoice')) ?>" target="_blank" rel="noopener">Facture</a>
                        <a class="btn btn-outline" href="<?= e(url('/deliveries?order_id=' . $o['id'])) ?>">Livraison</a>
                        <?php if (!in_array($o['status'], ['validated', 'prepared', 'shipped', 'delivered'], true)): ?>
                            <form method="post" action="<?= e(url('/orders/' . $o['id'] . '/validate')) ?>">
                                <?= csrf_field() ?>
                                <button class="btn btn-outline" type="submit">Valider</button>
                            </form>
                        <?php endif; ?>
                        <?php if (in_array($o['status'], ['validated', 'pending'], true)): ?>
                            <form method="post" action="<?= e(url('/orders/' . $o['id'] . '/prepare')) ?>" class="inline-form">
                                <?= csrf_field() ?>
                                <select name="warehouse_id">
                                    <?php foreach ($warehouses as $w): ?>
                                        <option value="<?= (int) $w['id'] ?>"><?= e($w['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <select name="method">
                                    <option value="FIFO">FIFO</option>
                                    <option value="LIFO">LIFO</option>
                                </select>
                                <button class="btn btn-outline" type="submit">Preparer</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
