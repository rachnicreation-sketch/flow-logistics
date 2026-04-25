<section class="page-header">
    <h2>Gestion Multi-Entreprise</h2>
    <p>Total: <?= (int) $stats['total'] ?> | Actives: <?= (int) $stats['active'] ?></p>
</section>

<section class="split-grid">
    <article class="panel">
        <h3>Nouvelle entreprise</h3>
        <form method="post" action="<?= e(url('/companies')) ?>" class="grid-form">
            <?= csrf_field() ?>
            <label>Nom<input type="text" name="name" required></label>
            <label>Code<input type="text" name="code" required></label>
            <label>Email<input type="email" name="email"></label>
            <label>Téléphone<input type="text" name="phone"></label>
            <label>Adresse<textarea name="address"></textarea></label>
            <label>Statut
                <select name="status">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </label>
            <button class="btn">Créer entreprise</button>
        </form>
    </article>
    <article class="panel">
        <h3>Liste des entreprises</h3>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Code</th><th>Nom</th><th>Email</th><th>Statut</th></tr></thead>
                <tbody>
                <?php foreach ($companies as $c): ?>
                    <tr>
                        <td><?= e($c['code']) ?></td>
                        <td><?= e($c['name']) ?></td>
                        <td><?= e($c['email']) ?></td>
                        <td><span class="badge"><?= e($c['status']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>

