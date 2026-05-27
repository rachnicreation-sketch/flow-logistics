<section class="page-header">
    <div class="header-left">
        <h2>Entrepôts (WMS)</h2>
        <p>Gérez vos infrastructures logistiques, zones et emplacements.</p>
    </div>
    <div class="header-right">
        <button class="btn btn-outline" onclick="document.getElementById('newZonePanel').classList.toggle('hidden')">
            <i class="fa-solid fa-layer-group"></i> Nouvelle Zone
        </button>
        <button class="btn btn-outline" onclick="document.getElementById('newLocationPanel').classList.toggle('hidden')">
            <i class="fa-solid fa-map-pin"></i> Nouvel Emplacement
        </button>
        <button class="btn btn-primary" onclick="document.getElementById('newWarehousePanel').classList.toggle('hidden')">
            <i class="fa-solid fa-warehouse"></i> Nouvel Entrepôt
        </button>
    </div>
</section>

<div class="mb-6">
    <section id="newWarehousePanel" class="panel panel-pad hidden mb-4">
        <h3>Créer un nouvel entrepôt</h3>
        <form method="post" action="<?= e(url('/warehouses')) ?>" class="grid-form-3">
            <?= csrf_field() ?>
            <label>Nom<input type="text" name="name" required placeholder="Ex: Entrepôt Central Lyon"></label>
            <label>Code<input type="text" name="code" required placeholder="WH-LYN-01"></label>
            <label>Adresse<textarea name="address" placeholder="Adresse complète..."></textarea></label>
            <div class="full-width mt-4">
                <button class="btn btn-primary" type="submit">Créer l'entrepôt</button>
            </div>
        </form>
    </section>

    <div class="split-grid hidden mb-4" id="newZonePanel">
        <article class="panel panel-pad">
            <h3>Ajouter une zone</h3>
            <form method="post" action="<?= e(url('/warehouses/zones')) ?>" class="grid-form">
                <?= csrf_field() ?>
                <label>Entrepôt
                    <select name="warehouse_id">
                        <?php foreach ($warehouses as $w): ?>
                            <option value="<?= (int) $w['id'] ?>"><?= e($w['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Nom de la zone<input type="text" name="name" required placeholder="Ex: Zone A - Réception"></label>
                <button class="btn btn-blue" type="submit">Enregistrer la zone</button>
            </form>
        </article>
    </div>

    <section id="newLocationPanel" class="panel panel-pad hidden mb-4">
        <h3>Ajouter un emplacement</h3>
        <form method="post" action="<?= e(url('/warehouses/locations')) ?>" class="grid-form-3">
            <?= csrf_field() ?>
            <label>Zone de destination
                <select name="zone_id">
                    <?php foreach ($zonesByWarehouse as $warehouseId => $zones): ?>
                        <?php foreach ($zones as $zone): ?>
                            <option value="<?= (int) $zone['id'] ?>">
                                <?= e($zone['name']) ?> (Entrepôt #<?= (int) $warehouseId ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Label / Code Emplacement<input type="text" name="label" required placeholder="Ex: A-01"></label>
            <label>Capacité (m3/kg)<input type="number" step="0.01" name="capacity" placeholder="0.00"></label>
            <div class="full-width mt-4">
                <button class="btn btn-primary" type="submit">Créer l'emplacement</button>
            </div>
        </form>
    </section>
</div>

<section class="split-grid">
<section class="panel">
    <div class="panel-header"><h3><i class="fa-solid fa-list"></i> Liste des Entrepôts et Zones</h3></div>
    <div class="panel-body grid-3">
    <?php foreach ($warehouses as $w): ?>
        <div class="warehouse-card">
            <div class="warehouse-card-header">
                <h4><a href="<?= e(url('/warehouses/' . $w['id'])) ?>"><i class="fa-solid fa-building"></i> <?= e($w['name']) ?></a></h4>
                <div style="display: flex; gap: 5px; align-items: center;">
                    <span class="badge badge-info"><?= e($w['code']) ?></span>
                    <form method="post" action="<?= e(url('/warehouses/delete/' . $w['id'])) ?>" style="display:inline;" onsubmit="return confirm('Voulez-vous vraiment supprimer cet entrepôt ?');">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-outline btn-sm btn-danger-text" title="Supprimer" style="padding: 2px 6px; border: none; background: transparent;"><i class="fa-solid fa-trash"></i></button>
                    </form>
                </div>
            </div>
            <p class="muted small mb-3"><i class="fa-solid fa-location-dot"></i> <?= e($w['address'] ?: 'Pas d\'adresse') ?></p>
            <ul class="compact-list">
                <?php foreach ($zonesByWarehouse[(int) $w['id']] ?? [] as $z): ?>
                    <li><i class="fa-solid fa-layer-group"></i> <?= e($z['name']) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endforeach; ?>
    </div>
</section>
    <article class="panel">
        <div class="panel-header"><h3>Emplacements</h3></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Entrepôt</th><th>Zone</th><th>Label</th><th>Capacité</th><th>Detail</th></tr></thead>
                <tbody>
                <?php foreach ($locations as $l): ?>
                    <tr>
                        <td><?= e($l['warehouse_name']) ?></td>
                        <td><?= e($l['zone_name']) ?></td>
                        <td><?= e($l['label']) ?></td>
                        <td><?= e((string) $l['capacity']) ?></td>
                        <td>
                            <a class="btn btn-outline btn-sm" href="<?= e(url('/locations/' . $l['id'])) ?>">Voir</a>
                            <form method="post" action="<?= e(url('/locations/delete/' . $l['id'])) ?>" style="display:inline;" onsubmit="return confirm('Voulez-vous vraiment supprimer cet emplacement ?');">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-outline btn-sm btn-danger-text" title="Supprimer" style="padding: 2px 6px; border: none; background: transparent;"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>
