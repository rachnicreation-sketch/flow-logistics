<section class="page-header">
    <div class="header-left">
        <h2>Véhicules</h2>
        <p>Gestion de la flotte et suivi de la capacité de transport.</p>
    </div>
    <div class="header-right">
        <button class="btn btn-primary" onclick="document.getElementById('newVehiclePanel').classList.toggle('hidden')">
            <i class="fa-solid fa-plus"></i> Nouveau Véhicule
        </button>
    </div>
</section>

<section id="newVehiclePanel" class="panel panel-pad hidden mb-6">
    <h3>Ajouter un nouveau véhicule</h3>
    <form method="post" action="<?= e(url('/vehicles')) ?>" class="grid-form-3">
        <?= csrf_field() ?>
        <label>Plaque d'immatriculation<input type="text" name="plate_number" required placeholder="Ex: FL-001-AB"></label>
        <label>Modèle<input type="text" name="model" placeholder="Ex: Mercedes Sprinter"></label>
        <label>Capacité (kg)<input type="number" step="0.01" name="capacity" placeholder="1500"></label>
        <label>Chauffeur assigné
            <select name="driver_id">
                <option value="">Aucun</option>
                <?php foreach ($drivers as $d): ?>
                    <option value="<?= (int) $d['id'] ?>"><?= e($d['name']) ?> (<?= e($d['role_name']) ?>)</option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Statut
            <select name="status">
                <option value="available">Disponible</option>
                <option value="maintenance">En maintenance</option>
                <option value="inactive">Inactif</option>
            </select>
        </label>
        <div class="full-width">
            <button class="btn btn-primary" type="submit">Enregistrer le véhicule</button>
        </div>
    </form>
</section>

<section class="panel">
    <div class="panel-header">
        <h3>Flotte de véhicules</h3>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Plaque</th><th>Modèle</th><th>Capacité</th><th>Statut</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($vehicles as $v): ?>
                <tr>
                    <td><strong><?= e($v['plate_number']) ?></strong></td>
                    <td><?= e($v['model'] ?? '-') ?></td>
                    <td><?= $v['capacity'] ? e(number_format((float)$v['capacity'], 2)) . ' kg' : '-' ?></td>
                    <td>
                        <span class="badge badge-<?= $v['status'] === 'available' ? 'success' : ($v['status'] === 'maintenance' ? 'warning' : 'danger') ?>">
                            <?= e($v['status']) ?>
                        </span>
                    </td>
                    <td class="action-row">
                        <a class="btn btn-outline btn-sm" href="<?= e(url('/vehicles/edit/' . $v['id'])) ?>"><i class="fa-solid fa-pen-to-square"></i> Modifier</a>
                        <form method="post" action="<?= e(url('/vehicles/delete/' . $v['id'])) ?>" style="display:inline;" onsubmit="return confirm('Supprimer ce véhicule ?');">
                            <?= csrf_field() ?>
                            <button class="btn btn-outline btn-sm btn-danger-text" type="submit"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
