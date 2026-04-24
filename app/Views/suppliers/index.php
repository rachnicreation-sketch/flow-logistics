<section class="page-header">
    <h2>Fournisseurs</h2>
    <p>Gestion des partenaires et suivi relation fournisseur.</p>
</section>

<section class="split-grid">
    <article class="panel panel-pad">
        <h3>Nouveau fournisseur</h3>
        <form method="post" action="<?= e(url('/suppliers')) ?>" class="grid-form">
            <?= csrf_field() ?>
            <label>Nom<input type="text" name="name" required></label>
            <label>Contact<input type="text" name="contact_name"></label>
            <label>Email<input type="email" name="email"></label>
            <label>Telephone<input type="text" name="phone"></label>
            <label>Adresse<textarea name="address"></textarea></label>
            <label>Note fournisseur (0-5)<input type="number" step="0.1" min="0" max="5" name="rating"></label>
            <button class="btn" type="submit">Ajouter</button>
        </form>
    </article>
    <article class="panel">
        <div class="panel-header">
            <h3>Liste fournisseurs</h3>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Nom</th><th>Contact</th><th>Email</th><th>Telephone</th><th>Rating</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($suppliers as $s): ?>
                    <tr>
                        <td><?= e($s['name']) ?></td>
                        <td><?= e($s['contact_name']) ?></td>
                        <td><?= e($s['email']) ?></td>
                        <td><?= e($s['phone']) ?></td>
                        <td><?= e((string) $s['rating']) ?></td>
                        <td class="action-row">
                            <a class="btn btn-outline btn-sm" href="<?= e(url('/suppliers/' . $s['id'])) ?>">Detail / Modifier</a>
                            <a class="btn btn-outline btn-sm" href="<?= e(url('/suppliers/' . $s['id'] . '/history')) ?>">Historique</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>
