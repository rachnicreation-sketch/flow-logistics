<section class="page-header">
    <div class="header-left">
        <h2>Fournisseurs</h2>
        <p>Gestion des partenaires et suivi relation fournisseur.</p>
    </div>
    <div class="header-right">
        <button class="btn btn-primary" onclick="document.getElementById('newSupplierPanel').classList.toggle('hidden')">
            <i class="fa-solid fa-plus"></i> Nouveau Fournisseur
        </button>
    </div>
</section>

<section id="newSupplierPanel" class="panel panel-pad hidden mb-6">
    <h3>Ajouter un nouveau fournisseur</h3>
    <form method="post" action="<?= e(url('/suppliers')) ?>" class="grid-form-3">
        <?= csrf_field() ?>
        <label>Nom<input type="text" name="name" required placeholder="Ex: TransEuro Supply"></label>
        <label>Contact<input type="text" name="contact_name" placeholder="Nom du contact"></label>
        <label>Email<input type="email" name="email" placeholder="email@exemple.com"></label>
        <label>Telephone<input type="text" name="phone" placeholder="+242 06 123 45 67"></label>
        <label>Note (0-5)<input type="number" step="0.1" min="0" max="5" name="rating" value="5.0"></label>
        <label class="full-width">Adresse<textarea name="address" placeholder="Adresse complète..."></textarea></label>
        <div class="full-width">
            <button class="btn btn-primary" type="submit">Enregistrer le fournisseur</button>
        </div>
    </form>
</section>

<section class="panel">
    <div class="panel-header">
        <h3>Liste des fournisseurs enregistrés</h3>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Nom</th><th>Contact</th><th>Email</th><th>Telephone</th><th>Rating</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($suppliers as $s): ?>
                <tr>
                    <td><strong><?= e($s['name']) ?></strong></td>
                    <td><?= e($s['contact_name']) ?></td>
                    <td><?= e($s['email']) ?></td>
                    <td><?= e($s['phone']) ?></td>
                    <td><span class="badge badge-success"><?= e((string) $s['rating']) ?> / 5</span></td>
                    <td class="action-row">
                        <a class="btn btn-outline btn-sm" href="<?= e(url('/suppliers/' . $s['id'])) ?>"><i class="fa-solid fa-pen-to-square"></i> Modifier</a>
                        <a class="btn btn-outline btn-sm" href="<?= e(url('/suppliers/' . $s['id'] . '/history')) ?>"><i class="fa-solid fa-history"></i> Historique</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
