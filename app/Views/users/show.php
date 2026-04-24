<?php
$roleLabel = static function (string $slug): string {
    return match ($slug) {
        'dg' => 'Directeur General (DG)',
        'dm', 'company_admin' => 'Directeur Manager (DM)',
        'logistics_manager' => 'Responsable Logistique (RL)',
        'storekeeper' => 'Magasinier',
        'driver' => 'Chauffeur',
        default => $slug,
    };
};
?>
<section class="page-header">
    <div>
        <h2>Utilisateur: <?= e($targetUser['name']) ?></h2>
        <p>Modifier les informations, le role et le statut.</p>
    </div>
    <div class="action-row">
        <a class="btn btn-outline" href="<?= e(url('/users')) ?>">Retour</a>
    </div>
</section>

<section class="panel panel-pad">
    <form method="post" action="<?= e(url('/users/' . $targetUser['id'] . '/update')) ?>" class="grid-form">
        <?= csrf_field() ?>
        <label>Nom
            <input type="text" name="name" value="<?= e($targetUser['name']) ?>" required>
        </label>
        <label>Email
            <input type="email" name="email" value="<?= e($targetUser['email']) ?>" required>
        </label>
        <label>Telephone
            <input type="text" name="phone" value="<?= e($targetUser['phone'] ?? '') ?>">
        </label>
        <label>Nouveau mot de passe (laisser vide pour conserver)
            <input type="password" name="password">
        </label>
        <label>Role
            <select name="role_id">
                <?php foreach ($roles as $r): ?>
                    <option value="<?= (int) $r['id'] ?>" <?= (int) $r['id'] === (int) $targetUser['role_id'] ? 'selected' : '' ?>>
                        <?= e($roleLabel((string) $r['slug'])) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Actif
            <select name="is_active">
                <option value="1" <?= (int) $targetUser['is_active'] === 1 ? 'selected' : '' ?>>Oui</option>
                <option value="0" <?= (int) $targetUser['is_active'] === 0 ? 'selected' : '' ?>>Non</option>
            </select>
        </label>
        <div class="action-row">
            <button class="btn" type="submit">Enregistrer</button>
        </div>
    </form>
</section>

<?php if (!empty($canDelete)): ?>
<section class="panel panel-pad">
    <h3>Suppression (DG uniquement)</h3>
    <form method="post" action="<?= e(url('/users/' . $targetUser['id'] . '/delete')) ?>">
        <?= csrf_field() ?>
        <button class="btn btn-danger" type="submit">Supprimer cet utilisateur</button>
    </form>
</section>
<?php endif; ?>
