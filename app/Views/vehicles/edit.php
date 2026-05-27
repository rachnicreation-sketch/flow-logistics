<section class="page-header">
    <div class="header-left">
        <h2>Modifier le véhicule</h2>
        <p>Plaque : <?= e($vehicle['plate_number']) ?></p>
    </div>
    <div class="header-right">
        <a href="<?= e(url('/vehicles')) ?>" class="btn btn-outline">
            <i class="fa-solid fa-arrow-left"></i> Retour
        </a>
    </div>
</section>

<section class="panel panel-pad">
    <form method="post" action="<?= e(url('/vehicles/update/' . $vehicle['id'])) ?>" class="grid-form-3">
        <?= csrf_field() ?>
        <label>Plaque d'immatriculation<input type="text" name="plate_number" required value="<?= e($vehicle['plate_number']) ?>"></label>
        <label>Modèle<input type="text" name="model" value="<?= e($vehicle['model'] ?? '') ?>"></label>
        <label>Capacité (kg)<input type="number" step="0.01" name="capacity" value="<?= e((string)$vehicle['capacity'] ?? '') ?>"></label>
        <label>Chauffeur assigné
            <select name="driver_id">
                <option value="">Aucun</option>
                <?php foreach ($drivers as $d): ?>
                    <option value="<?= (int) $d['id'] ?>" <?= ($vehicle['driver_id'] ?? null) == $d['id'] ? 'selected' : '' ?>><?= e($d['name']) ?> (<?= e($d['role_name']) ?>)</option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Statut
            <select name="status">
                <option value="available" <?= $vehicle['status'] === 'available' ? 'selected' : '' ?>>Disponible</option>
                <option value="maintenance" <?= $vehicle['status'] === 'maintenance' ? 'selected' : '' ?>>En maintenance</option>
                <option value="inactive" <?= $vehicle['status'] === 'inactive' ? 'selected' : '' ?>>Inactif</option>
            </select>
        </label>
        <div class="full-width">
            <button class="btn btn-primary" type="submit">Mettre à jour le véhicule</button>
        </div>
    </form>
</section>
