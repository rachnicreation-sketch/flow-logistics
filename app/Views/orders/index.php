<section class="page-header">
    <div>
        <h2>Commandes clients</h2>
        <p>Creation, validation, preparation, facturation et transfert vers livraison.</p>
    </div>
    <div class="action-row">
        <a class="btn btn-outline" href="<?= e(url('/deliveries')) ?>">Aller aux livraisons</a>
    </div>
</section>

<section class="tri-grid">
    <article class="panel panel-pad">
        <h3>Nouveau client</h3>
        <form method="post" action="<?= e(url('/orders/customers')) ?>" class="grid-form">
            <?= csrf_field() ?>
            <label>Nom<input type="text" name="name" required></label>
            <label>Email<input type="email" name="email"></label>
            <label>Telephone<input type="text" name="phone"></label>
            <label>Adresse<textarea name="address"></textarea></label>
            <button class="btn" type="submit">Creer client</button>
        </form>
    </article>

    <article class="panel panel-pad">
        <h3>Nouvelle commande</h3>
        <form method="post" action="<?= e(url('/orders')) ?>" class="grid-form">
            <?= csrf_field() ?>
            <label>Client
                <select name="customer_id">
                    <?php foreach ($customers as $c): ?>
                        <option value="<?= (int) $c['id'] ?>"><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Reference commande<input type="text" name="reference" value="SO-<?= date('YmdHis') ?>" required></label>
            <label>Adresse livraison<textarea name="delivery_address"></textarea></label>
            <div class="item-rows" data-module="order">
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
                <button class="btn" type="submit">Creer commande</button>
            </div>
        </form>
    </article>

    <article class="panel panel-pad">
        <h3>Liens module</h3>
        <p class="muted-text">Pilotez le cycle complet commande vers expedition.</p>
        <div class="action-column">
            <a class="btn btn-outline" href="<?= e(url('/customers')) ?>">Base clients</a>
            <a class="btn btn-outline" href="<?= e(url('/deliveries')) ?>">Planifier les livraisons</a>
            <a class="btn btn-outline" href="<?= e(url('/reports/orders')) ?>" target="_blank" rel="noopener">Rapport commandes PDF</a>
        </div>
    </article>
</section>

<section class="panel">
    <div class="panel-header">
        <h3>Suivi des commandes</h3>
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
                    <td><?= number_format((float) $o['total_amount'], 2, ',', ' ') ?></td>
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
