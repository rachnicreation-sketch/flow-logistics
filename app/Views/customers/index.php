<section class="page-header">
    <h2>Clients</h2>
    <p>Référentiel client pour commandes et livraisons.</p>
</section>

<section class="split-grid">
    <article class="panel panel-pad">
        <h3>Nouveau client</h3>
        <form method="post" action="<?= e(url('/customers')) ?>" class="grid-form">
            <?= csrf_field() ?>
            <label>Nom<input type="text" name="name" required></label>
            <label>Email<input type="email" name="email"></label>
            <label>Telephone<input type="text" name="phone"></label>
            <label>Adresse<textarea name="address"></textarea></label>
            <button class="btn" type="submit">Ajouter client</button>
        </form>
    </article>

    <article class="panel">
        <div class="panel-header">
            <h3>Base clients</h3>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Nom</th><th>Email</th><th>Telephone</th><th>Adresse</th><th>Action</th></tr></thead>
                <tbody>
                <?php foreach ($customers as $c): ?>
                    <tr>
                        <td><?= e($c['name']) ?></td>
                        <td><?= e($c['email']) ?></td>
                        <td><?= e($c['phone']) ?></td>
                        <td><?= e($c['address']) ?></td>
                        <td>
                            <a class="btn btn-outline btn-sm" href="<?= e(url('/customers/' . $c['id'])) ?>">Detail / Modifier</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>
