<section class="page-header">
    <div>
        <h2>Client: <?= e($customer['name']) ?></h2>
        <p>Modifier les informations du client.</p>
    </div>
    <div class="action-row">
        <a class="btn btn-outline" href="<?= e(url('/customers')) ?>">Retour</a>
    </div>
</section>

<section class="panel panel-pad">
    <form method="post" action="<?= e(url('/customers/' . $customer['id'] . '/update')) ?>" class="grid-form">
        <?= csrf_field() ?>
        <label>Nom
            <input type="text" name="name" value="<?= e($customer['name']) ?>" required>
        </label>
        <label>Email
            <input type="email" name="email" value="<?= e($customer['email'] ?? '') ?>">
        </label>
        <label>Telephone
            <input type="text" name="phone" value="<?= e($customer['phone'] ?? '') ?>">
        </label>
        <label>Adresse
            <textarea name="address"><?= e($customer['address'] ?? '') ?></textarea>
        </label>
        <button class="btn" type="submit">Enregistrer</button>
    </form>
</section>

<?php if (!empty($canDelete)): ?>
<section class="panel panel-pad">
    <h3>Suppression (DG uniquement)</h3>
    <form method="post" action="<?= e(url('/customers/' . $customer['id'] . '/delete')) ?>">
        <?= csrf_field() ?>
        <button class="btn btn-danger" type="submit">Supprimer ce client</button>
    </form>
</section>
<?php endif; ?>
