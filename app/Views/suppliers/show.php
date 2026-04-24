<section class="page-header">
    <div>
        <h2>Fournisseur: <?= e($supplier['name']) ?></h2>
        <p>Modifier les informations et consulter les achats associes.</p>
    </div>
    <div class="action-row">
        <a class="btn btn-outline" href="<?= e(url('/suppliers')) ?>">Retour</a>
    </div>
</section>

<section class="split-grid">
    <article class="panel panel-pad">
        <h3>Edition fournisseur</h3>
        <form method="post" action="<?= e(url('/suppliers/' . $supplier['id'] . '/update')) ?>" class="grid-form">
            <?= csrf_field() ?>
            <label>Nom
                <input type="text" name="name" value="<?= e($supplier['name']) ?>" required>
            </label>
            <label>Contact
                <input type="text" name="contact_name" value="<?= e($supplier['contact_name'] ?? '') ?>">
            </label>
            <label>Email
                <input type="email" name="email" value="<?= e($supplier['email'] ?? '') ?>">
            </label>
            <label>Telephone
                <input type="text" name="phone" value="<?= e($supplier['phone'] ?? '') ?>">
            </label>
            <label>Adresse
                <textarea name="address"><?= e($supplier['address'] ?? '') ?></textarea>
            </label>
            <label>Rating
                <input type="number" step="0.1" min="0" max="5" name="rating" value="<?= e((string) ($supplier['rating'] ?? 0)) ?>">
            </label>
            <button class="btn" type="submit">Enregistrer</button>
        </form>
    </article>

    <article class="panel">
        <div class="panel-header">
            <h3>Historique des achats</h3>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Reference</th><th>Statut</th><th>Montant</th><th>Date</th></tr></thead>
                <tbody>
                <?php if (empty($history)): ?>
                    <tr><td colspan="4" class="empty-row">Aucun achat lie a ce fournisseur.</td></tr>
                <?php else: ?>
                    <?php foreach ($history as $row): ?>
                    <tr>
                        <td><?= e($row['reference']) ?></td>
                        <td><?= e($row['status']) ?></td>
                        <td><?= number_format((float) $row['total_amount'], 2, ',', ' ') ?></td>
                        <td><?= e($row['created_at']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>

<?php if (!empty($canDelete)): ?>
<section class="panel panel-pad">
    <h3>Suppression (DG uniquement)</h3>
    <form method="post" action="<?= e(url('/suppliers/' . $supplier['id'] . '/delete')) ?>">
        <?= csrf_field() ?>
        <button class="btn btn-danger" type="submit">Supprimer ce fournisseur</button>
    </form>
</section>
<?php endif; ?>
