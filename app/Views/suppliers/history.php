<section class="page-header">
    <h2>Historique fournisseur</h2>
    <p><?= e($supplier['name']) ?></p>
</section>

<section class="panel">
    <h3>Transactions (achats)</h3>
    <div class="table-wrap">
        <table>
            <thead><tr><th>RÃƒÂ©fÃƒÂ©rence</th><th>Statut</th><th>Montant</th><th>Date</th></tr></thead>
            <tbody>
            <?php foreach ($history as $row): ?>
                <tr>
                    <td><?= e($row['reference']) ?></td>
                    <td><span class="badge"><?= e($row['status']) ?></span></td>
                    <td><?= number_format((float) $row['total_amount'], 2, ',', ' ') ?></td>
                    <td><?= e($row['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <p style="margin-top: .8rem;"><a class="btn btn-outline" href="<?= e(url('/suppliers')) ?>">Retour fournisseurs</a></p>
</section>

