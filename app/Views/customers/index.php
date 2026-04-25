<section class="page-header">
    <div class="header-left">
        <h2>Gestion des Clients</h2>
        <p>Référentiel client pour la gestion des commandes et le suivi des livraisons.</p>
    </div>
    <div class="header-right">
        <button class="btn btn-primary" onclick="document.getElementById('newCustomerPanel').classList.toggle('hidden')">
            <i class="fa-solid fa-user-tag"></i> Nouveau Client
        </button>
    </div>
</section>

<section id="newCustomerPanel" class="panel panel-pad hidden mb-6">
    <h3>Ajouter un nouveau client</h3>
    <form method="post" action="<?= e(url('/customers')) ?>" class="grid-form-3">
        <?= csrf_field() ?>
        <label>Nom de l'entreprise / Client<input type="text" name="name" required placeholder="Ex: LogiCorp SARL"></label>
        <label>Email de contact<input type="email" name="email" placeholder="contact@client.com"></label>
        <label>Téléphone<input type="text" name="phone" placeholder="+242 06 999 88 77"></label>
        <label class="full-width">Adresse de livraison<textarea name="address" placeholder="Adresse complète du client..."></textarea></label>
        <div class="full-width mt-4">
            <button class="btn btn-primary" type="submit">Enregistrer le client</button>
        </div>
    </form>
</section>

<section class="panel">
    <div class="panel-header">
        <h3><i class="fa-solid fa-address-book"></i> Base de données Clients</h3>
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
