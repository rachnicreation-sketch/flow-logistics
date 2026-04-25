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
    <div class="header-left">
        <h2>Gestion des Utilisateurs</h2>
        <p>Gérez les accès, les rôles et les profils de votre équipe logistique.</p>
    </div>
    <div class="header-right">
        <button class="btn btn-primary" onclick="document.getElementById('newUserPanel').classList.toggle('hidden')">
            <i class="fa-solid fa-user-plus"></i> Nouvel Utilisateur
        </button>
    </div>
</section>

<section id="newUserPanel" class="panel panel-pad hidden mb-6">
    <h3>Enregistrer un nouvel utilisateur</h3>
    <form method="post" action="<?= e(url('/users')) ?>" class="grid-form-3">
        <?= csrf_field() ?>
        <label>Nom complet<input type="text" name="name" required placeholder="Ex: Jean Dupont"></label>
        <label>Email professionnel<input type="email" name="email" required placeholder="jean@flow-logistics.com"></label>
        <label>Téléphone<input type="text" name="phone" placeholder="+242 05 555 44 33"></label>
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
        <label>Rôle / Fonction
            <select name="role_id">
                <?php foreach ($roles as $r): ?>
                    <option value="<?= (int) $r['id'] ?>"><?= e($roleLabel((string) $r['slug'])) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <div class="full-width mt-4">
            <button class="btn btn-primary" type="submit">Créer l'utilisateur</button>
        </div>
    </form>
</section>

<section class="panel">
    <div class="panel-header">
        <h3><i class="fa-solid fa-users"></i> Membres de l'équipe</h3>
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
