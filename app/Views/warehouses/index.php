<section class="page-header">
    <h2>Entrepots (WMS)</h2>
    <p>Entrepots, zones et emplacements avec fiches detaillees.</p>
</section>

<section class="tri-grid">
    <article class="panel panel-pad">
        <h3>Creer entrepot</h3>
        <form method="post" action="<?= e(url('/warehouses')) ?>" class="grid-form">
            <?= csrf_field() ?>
            <label>Nom<input type="text" name="name" required></label>
            <label>Code<input type="text" name="code" required></label>
            <label>Adresse<textarea name="address"></textarea></label>
            <button class="btn" type="submit">Creer</button>
        </form>
    </article>
    <article class="panel panel-pad">
        <h3>Ajouter zone</h3>
        <form method="post" action="<?= e(url('/warehouses/zones')) ?>" class="grid-form">
            <?= csrf_field() ?>
            <label>Entrepot
                <select name="warehouse_id">
                    <?php foreach ($warehouses as $w): ?>
                        <option value="<?= (int) $w['id'] ?>"><?= e($w['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Nom zone<input type="text" name="name" required></label>
            <button class="btn" type="submit">Ajouter zone</button>
        </form>
    </article>
    <article class="panel panel-pad">
        <h3>Ajouter emplacement</h3>
        <form method="post" action="<?= e(url('/warehouses/locations')) ?>" class="grid-form">
            <?= csrf_field() ?>
            <label>Zone
                <select name="zone_id">
                    <?php foreach ($zonesByWarehouse as $warehouseId => $zones): ?>
                        <?php foreach ($zones as $zone): ?>
                            <option value="<?= (int) $zone['id'] ?>">
                                <?= e($zone['name']) ?> (Entrepot #<?= (int) $warehouseId ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Label<input type="text" name="label" required></label>
            <label>Capacite<input type="number" step="0.01" name="capacity"></label>
            <button class="btn" type="submit">Ajouter emplacement</button>
        </form>
    </article>
</section>

<section class="split-grid">
    <article class="panel">
        <div class="panel-header"><h3>Entrepots et zones</h3></div>
        <div class="panel-body">
        <?php foreach ($warehouses as $w): ?>
            <div class="cluster">
                <h4><a href="<?= e(url('/warehouses/' . $w['id'])) ?>"><?= e($w['name']) ?> (<?= e($w['code']) ?>)</a></h4>
                <ul class="compact-list">
                    <?php foreach ($zonesByWarehouse[(int) $w['id']] ?? [] as $z): ?>
                        <li><?= e($z['name']) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>
        </div>
    </article>
    <article class="panel">
        <div class="panel-header"><h3>Emplacements</h3></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Entrepot</th><th>Zone</th><th>Label</th><th>Capacite</th><th>Detail</th></tr></thead>
                <tbody>
                <?php foreach ($locations as $l): ?>
                    <tr>
                        <td><?= e($l['warehouse_name']) ?></td>
                        <td><?= e($l['zone_name']) ?></td>
                        <td><?= e($l['label']) ?></td>
                        <td><?= e((string) $l['capacity']) ?></td>
                        <td><a class="btn btn-outline btn-sm" href="<?= e(url('/locations/' . $l['id'])) ?>">Voir</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>
