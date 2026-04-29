<section class="page-header">
    <div class="header-left">
        <h2>Inventaire : <?= e($inventory['title']) ?></h2>
        <p>Entrepôt : <?= e($warehouse['name']) ?> | Statut : 
            <span class="badge badge-<?= $inventory['status'] === 'open' ? 'warning' : 'success' ?>">
                <?= $inventory['status'] === 'open' ? 'En cours' : 'Clôturé' ?>
            </span>
        </p>
    </div>
    <div class="header-right">
        <a href="<?= e(url('/inventories')) ?>" class="btn btn-outline">
            <i class="fa-solid fa-arrow-left"></i> Retour
        </a>
        <?php if ($inventory['status'] === 'open'): ?>
            <form method="post" action="<?= e(url('/inventories/close/' . $inventory['id'])) ?>" style="display:inline;" onsubmit="return confirm('Clôturer l\'inventaire ? Les stocks seront ajustés définitivement.');">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-primary btn-success-bg">
                    <i class="fa-solid fa-check-double"></i> Clôturer l'inventaire
                </button>
            </form>
        <?php endif; ?>
    </div>
</section>

<section class="panel">
    <div class="panel-header">
        <h3>Saisie des quantités physiques</h3>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Produit</th>
                    <th>Emplacement</th>
                    <th>Théorique</th>
                    <th>Physique</th>
                    <th>Écart</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td>
                        <strong><?= e($item['product_name']) ?></strong><br>
                        <small class="text-muted"><?= e($item['sku']) ?></small>
                    </td>
                    <td><?= e($item['location_label'] ?? 'Zone commune') ?></td>
                    <td><?= e(number_format((float)$item['theoretical_quantity'], 2)) ?></td>
                    <td>
                        <?php if ($inventory['status'] === 'open'): ?>
                            <form method="post" action="<?= e(url('/inventories/update-item')) ?>" class="inline-form">
                                <?= csrf_field() ?>
                                <input type="hidden" name="inventory_id" value="<?= $inventory['id'] ?>">
                                <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                <input type="number" step="0.01" name="actual_quantity" value="<?= $item['actual_quantity'] ?>" class="input-sm" style="width: 100px;">
                                <button type="submit" class="btn-icon-only" title="Enregistrer"><i class="fa-solid fa-save"></i></button>
                            </form>
                        <?php else: ?>
                            <?= $item['actual_quantity'] !== null ? e(number_format((float)$item['actual_quantity'], 2)) : '-' ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($item['actual_quantity'] !== null): ?>
                            <span class="badge badge-<?= (float)$item['difference'] == 0 ? 'success' : 'danger' ?>">
                                <?= ($item['difference'] > 0 ? '+' : '') . e(number_format((float)$item['difference'], 2)) ?>
                            </span>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td>
                        <!-- Notes ou Historique spécifique -->
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<style>
.input-sm {
    padding: 4px 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}
.btn-icon-only {
    background: none;
    border: none;
    color: var(--primary);
    cursor: pointer;
    font-size: 1.1rem;
}
.btn-success-bg {
    background-color: #10b981 !important;
    border-color: #10b981 !important;
}
</style>
