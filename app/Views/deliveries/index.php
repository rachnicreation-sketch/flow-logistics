<section class="page-header">
    <div>
        <h2>Transport et livraisons (TMS)</h2>
        <p>Planification, affectation chauffeurs/véhicules et suivi de statut.</p>
    </div>
    <div class="action-row">
        <a class="btn btn-outline" href="<?= e(url('/orders')) ?>">Voir les commandes</a>
    </div>
</section>

<section class="tri-grid">
    <article class="panel panel-pad">
        <h3>Nouveau véhicule</h3>
        <form method="post" action="<?= e(url('/deliveries/vehicles')) ?>" class="grid-form">
            <?= csrf_field() ?>
            <label>Matricule<input type="text" name="plate_number" required></label>
            <label>Modele<input type="text" name="model"></label>
            <label>Capacité<input type="number" step="0.01" name="capacity"></label>
            <label>Statut
                <select name="status">
                    <option value="available">Disponible</option>
                    <option value="maintenance">Maintenance</option>
                </select>
            </label>
            <button class="btn" type="submit">Créer véhicule</button>
        </form>
    </article>

    <article class="panel panel-pad">
        <h3>Planifier une livraison</h3>
        <form method="post" action="<?= e(url('/deliveries')) ?>" class="grid-form">
            <?= csrf_field() ?>
            <label>Commande
                <select name="order_id">
                    <?php foreach ($orders as $o): ?>
                        <option value="<?= (int) $o['id'] ?>" <?= (int) ($selectedOrderId ?? 0) === (int) $o['id'] ? 'selected' : '' ?>>
                            <?= e($o['reference']) ?> - <?= e($o['customer_name']) ?> (<?= e($o['status']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Véhicule
                <select name="vehicle_id">
                    <option value="">-</option>
                    <?php foreach ($vehicles as $v): ?>
                        <option value="<?= (int) $v['id'] ?>"><?= e($v['plate_number']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Chauffeur
                <select name="driver_id">
                    <option value="">-</option>
                    <?php foreach ($drivers as $d): ?>
                        <option value="<?= (int) $d['id'] ?>"><?= e($d['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Date prevue<input type="datetime-local" name="planned_date"></label>
            <label>Notes<textarea name="notes"></textarea></label>
            <button class="btn" type="submit">Planifier</button>
        </form>
    </article>

    <article class="panel panel-pad">
        <h3>Exports et reporting</h3>
        <p class="muted-text">Retrouvez les exports transport et les indicateurs d'exploitation.</p>
        <div class="action-column">
            <a class="btn btn-outline" href="<?= e(url('/reports/deliveries')) ?>" target="_blank" rel="noopener">Rapport livraisons PDF</a>
            <a class="btn btn-outline" href="<?= e(url('/reports')) ?>">Centre rapports</a>
        </div>
    </article>
</section>

<section class="panel">
    <div class="panel-header">
        <h3>Suivi des livraisons</h3>
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
