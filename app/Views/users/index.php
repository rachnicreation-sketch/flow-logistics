<?php

use App\Core\Auth;

$isSuper = (Auth::user()['role_slug'] ?? '') === 'super_admin';
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
    <h2>Utilisateurs et profils</h2>
    <p>Gestion des departements et droits operationnels.</p>
</section>

<section class="split-grid">
    <article class="panel panel-pad">
        <h3>Créer un utilisateur</h3>
        <form method="post" action="<?= e(url('/users')) ?>" class="grid-form">
            <?= csrf_field() ?>
            <label>Nom<input type="text" name="name" required></label>
            <label>Email<input type="email" name="email" required></label>
            <label>Telephone<input type="text" name="phone"></label>
            <label>Mot de passe<input type="password" name="password" required></label>
            <?php if ($isSuper): ?>
                <label>Entreprise
                    <select name="company_id">
                        <?php foreach ($companies as $c): ?>
                            <option value="<?= (int) $c['id'] ?>"><?= e($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            <?php endif; ?>
            <label>Role
                <select name="role_id">
                    <?php foreach ($roles as $r): ?>
                        <option value="<?= (int) $r['id'] ?>"><?= e($roleLabel((string) $r['slug'])) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button class="btn" type="submit">Créer</button>
        </form>
    </article>

    <article class="panel">
        <div class="panel-header">
            <h3>Liste utilisateurs</h3>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Nom</th><th>Email</th><th>Role</th><th>Actif</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= e($u['name']) ?></td>
                        <td><?= e($u['email']) ?></td>
                        <td><?= e($roleLabel((string) $u['role_slug'])) ?></td>
                        <td><?= (int) $u['is_active'] ? 'Oui' : 'Non' ?></td>
                        <td class="action-row">
                            <a class="btn btn-outline btn-sm" href="<?= e(url('/users/' . $u['id'])) ?>">Detail / Modifier</a>
                            <form method="post" action="<?= e(url('/users/' . $u['id'] . '/toggle')) ?>">
                                <?= csrf_field() ?>
                                <button class="btn btn-outline btn-sm" type="submit">Basculer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>
