<section class="page-header">
    <h2>Logs utilisateurs</h2>
    <p>TraÃƒÂ§abilitÃƒÂ© des opÃƒÂ©rations (audit trail).</p>
</section>

<section class="panel">
    <h3>Historique des actions</h3>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Date</th><th>Utilisateur</th><th>Action</th><th>Module</th><th>Entity</th><th>IP</th><th>MÃƒÂ©tadonnÃƒÂ©es</th></tr></thead>
            <tbody>
            <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?= e($log['created_at']) ?></td>
                    <td><?= e($log['user_name'] ?? '-') ?></td>
                    <td><?= e($log['action']) ?></td>
                    <td><?= e($log['module']) ?></td>
                    <td><?= e((string) ($log['entity_id'] ?? '-')) ?></td>
                    <td><?= e($log['ip_address'] ?? '-') ?></td>
                    <td><?= e((string) ($log['metadata_json'] ?? '-')) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

