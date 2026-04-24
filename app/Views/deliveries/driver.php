<section class="page-header">
    <div>
        <h2>Interface chauffeur</h2>
        <p>Mise a jour des livraisons affectees en temps reel.</p>
    </div>
    <div class="action-row">
        <a class="btn btn-outline btn-sm" href="<?= e(url('/messages')) ?>">Messages</a>
        <a class="btn btn-outline btn-sm" href="<?= e(url('/notifications')) ?>">Notifications</a>
    </div>
</section>

<section class="panel">
    <div class="panel-header">
        <h3>Mes livraisons</h3>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Commande</th><th>Client</th><th>Adresse</th><th>Statut</th><th>Mettre a jour</th></tr></thead>
            <tbody>
            <?php foreach ($deliveries as $d): ?>
                <tr>
                    <td><?= e($d['order_ref']) ?></td>
                    <td><?= e($d['customer_name']) ?></td>
                    <td><?= e($d['delivery_address']) ?></td>
                    <td><span class="badge"><?= e($d['status']) ?></span></td>
                    <td>
                        <form method="post" action="<?= e(url('/driver/deliveries/' . $d['id'] . '/status')) ?>" class="inline-form">
                            <?= csrf_field() ?>
                            <select name="status">
                                <option value="pending" <?= ($d['status'] === 'pending') ? 'selected' : '' ?>>En attente</option>
                                <option value="in_transit" <?= ($d['status'] === 'in_transit') ? 'selected' : '' ?>>En cours</option>
                                <option value="delivered" <?= ($d['status'] === 'delivered') ? 'selected' : '' ?>>Livre</option>
                                <option value="failed" <?= ($d['status'] === 'failed') ? 'selected' : '' ?>>Echec</option>
                            </select>
                            <input type="number" step="0.000001" name="lat" placeholder="Latitude">
                            <input type="number" step="0.000001" name="lng" placeholder="Longitude">
                            <input type="text" name="driver_notes" placeholder="Note chauffeur">
                            <button class="btn btn-outline" type="submit">Envoyer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
