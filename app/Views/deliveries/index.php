<section class="page-header">
    <div class="header-left">
        <h2>Transport et Livraisons (TMS)</h2>
        <p>Planification, affectation des chauffeurs et suivi des statuts de transport.</p>
    </div>
    <div class="header-right">
        <button class="btn btn-outline" onclick="document.getElementById('newVehiclePanel').classList.toggle('hidden')">
            <i class="fa-solid fa-truck"></i> Nouveau Véhicule
        </button>
        <button class="btn btn-primary" onclick="document.getElementById('newDeliveryPanel').classList.toggle('hidden')">
            <i class="fa-solid fa-calendar-plus"></i> Planifier Livraison
        </button>
    </div>
</section>

<div class="mb-6">
    <section id="newVehiclePanel" class="panel panel-pad hidden mb-4">
        <h3>Enregistrer un nouveau véhicule</h3>
        <form method="post" action="<?= e(url('/deliveries/vehicles')) ?>" class="grid-form-3">
            <?= csrf_field() ?>
            <label>Matricule / Plaque<input type="text" name="plate_number" required placeholder="Ex: AA-123-BB"></label>
            <label>Modèle / Marque<input type="text" name="model" placeholder="Ex: Renault Master"></label>
            <label>Capacité utile (kg/m3)<input type="number" step="0.01" name="capacity" placeholder="0.00"></label>
            <label>Statut initial
                <select name="status">
                    <option value="available">Disponible</option>
                    <option value="maintenance">En maintenance</option>
                </select>
            </label>
            <div class="full-width mt-4">
                <button class="btn btn-primary" type="submit">Créer le véhicule</button>
            </div>
        </form>
    </section>

    <section id="newDeliveryPanel" class="panel panel-pad hidden mb-4">
        <h3>Planifier une nouvelle livraison</h3>
        <form method="post" action="<?= e(url('/deliveries')) ?>" class="grid-form-3">
            <?= csrf_field() ?>
            <label>Commande à livrer
                <select name="order_id">
                    <?php foreach ($orders as $o): ?>
                        <option value="<?= (int) $o['id'] ?>" <?= (int) ($selectedOrderId ?? 0) === (int) $o['id'] ? 'selected' : '' ?>>
                            <?= e($o['reference']) ?> - <?= e($o['customer_name']) ?> (<?= e($o['status']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Véhicule assigné
                <select name="vehicle_id">
                    <option value="">Sélectionner un véhicule...</option>
                    <?php foreach ($vehicles as $v): ?>
                        <option value="<?= (int) $v['id'] ?>"><?= e($v['plate_number']) ?> (<?= e($v['model']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Chauffeur assigné
                <select name="driver_id">
                    <option value="">Sélectionner un chauffeur...</option>
                    <?php foreach ($drivers as $d): ?>
                        <option value="<?= (int) $d['id'] ?>"><?= e($d['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Date et heure prévues<input type="datetime-local" name="planned_date"></label>
            <label class="full-width">Notes de livraison<textarea name="notes" placeholder="Instructions pour le chauffeur..."></textarea></label>
            <div class="full-width mt-4">
                <button class="btn btn-primary" type="submit">Confirmer la planification</button>
            </div>
        </form>
    </section>
</div>

<section class="panel">
    <div class="panel-header">
        <h3><i class="fa-solid fa-truck-ramp-box"></i> Suivi opérationnel des livraisons</h3>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Commande</th><th>Client</th><th>Chauffeur</th><th>Véhicule</th><th>Statut</th><th>Planifie</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($deliveries as $d): ?>
                <tr>
                    <td><?= e($d['order_ref']) ?></td>
                    <td><?= e($d['customer_name']) ?></td>
                    <td><?= e($d['driver_name'] ?? '-') ?></td>
                    <td><?= e($d['plate_number'] ?? '-') ?></td>
                    <td><span class="badge"><?= e($d['status']) ?></span></td>
                    <td><?= e($d['planned_date'] ?? '-') ?></td>
                    <td>
                        <form method="post" action="<?= e(url('/deliveries/' . $d['id'] . '/status')) ?>" class="inline-form">
                            <?= csrf_field() ?>
                            <select name="status">
                                <option value="pending" <?= ($d['status'] === 'pending') ? 'selected' : '' ?>>En attente</option>
                                <option value="in_transit" <?= ($d['status'] === 'in_transit') ? 'selected' : '' ?>>En cours</option>
                                <option value="delivered" <?= ($d['status'] === 'delivered') ? 'selected' : '' ?>>Livre</option>
                                <option value="failed" <?= ($d['status'] === 'failed') ? 'selected' : '' ?>>Echec</option>
                            </select>
                            <input type="text" name="driver_notes" placeholder="Note interne">
                            <button class="btn btn-outline" type="submit">Mettre à jour</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
