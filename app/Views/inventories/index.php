<section class="page-header">
    <div class="header-left">
        <h2>Sessions d'Inventaire</h2>
        <p>Validez et ajustez vos stocks physiques par rapport aux stocks logiques.</p>
    </div>
    <div class="header-right">
        <button class="btn btn-primary" onclick="document.getElementById('newInventoryPanel').classList.toggle('hidden')">
            <i class="fa-solid fa-clipboard-list"></i> Nouvel Inventaire
        </button>
    </div>
</section>

<section id="newInventoryPanel" class="panel panel-pad hidden mb-6">
    <h3>Démarrer une session d'inventaire</h3>
    <form method="post" action="<?= e(url('/inventories')) ?>" class="grid-form-2">
        <?= csrf_field() ?>
        <label>Titre de l'inventaire<input type="text" name="title" required placeholder="Ex: Inventaire Annuel 2026"></label>
        <label>Entrepôt
            <select name="warehouse_id" required>
                <?php foreach ($warehouses as $w): ?>
                    <option value="<?= $w['id'] ?>"><?= e($w['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <div class="full-width">
            <button class="btn btn-primary" type="submit">Démarrer l'inventaire</button>
        </div>
    </form>
</section>

<section class="panel">
    <div class="panel-header">
        <h3>Historique des inventaires</h3>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Titre</th><th>Entrepôt</th><th>Statut</th><th>Date début</th><th>Créé par</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($inventories as $i): ?>
                <tr>
                    <td><strong><?= e($i['title']) ?></strong></td>
                    <td><?= e($i['warehouse_name']) ?></td>
                    <td>
                        <span class="badge badge-<?= $i['status'] === 'open' ? 'warning' : 'success' ?>">
                            <?= $i['status'] === 'open' ? 'En cours' : 'Clôturé' ?>
                        </span>
                    </td>
                    <td><?= date('d/m/Y H:i', strtotime($i['started_at'])) ?></td>
                    <td><?= e($i['creator_name'] ?? 'Système') ?></td>
                    <td class="action-row">
                        <a class="btn btn-outline btn-sm" href="<?= e(url('/inventories/show/' . $i['id'])) ?>">
                            <i class="fa-solid fa-eye"></i> <?= $i['status'] === 'open' ? 'Saisir' : 'Voir' ?>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
