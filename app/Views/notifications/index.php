<section class="page-header">
    <h2>Notifications</h2>
    <p>Alertes stock, commandes et événements système.</p>
</section>

<section class="panel">
    <h3>Centre de notifications</h3>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Date</th><th>Type</th><th>Titre</th><th>Message</th></tr></thead>
            <tbody>
            <?php foreach ($notifications as $n): ?>
                <tr>
                    <td><?= e($n['created_at']) ?></td>
                    <td><?= e($n['type']) ?></td>
                    <td><?= e($n['title']) ?></td>
                    <td><?= e($n['message']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

