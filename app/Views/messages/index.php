<section class="page-header">
    <div>
        <h2>Messagerie interne</h2>
        <p>Communication interne entre DG, DM, RL, Magasinier et Chauffeur.</p>
    </div>
    <div class="action-row">
        <span class="badge">Non lus: <?= (int) $unreadCount ?></span>
    </div>
</section>

<section class="split-grid">
    <?php if (!empty($canSend)): ?>
    <article class="panel panel-pad">
        <h3>Nouveau message</h3>
        <form method="post" action="<?= e(url('/messages')) ?>" class="grid-form">
            <?= csrf_field() ?>
            <label>Destinataire
                <select name="recipient_id" required>
                    <option value="">Choisir</option>
                    <?php foreach ($users as $u): ?>
                        <?php if ((int) $u['id'] === (int) $currentUserId) continue; ?>
                        <option value="<?= (int) $u['id'] ?>"><?= e($u['name']) ?> (<?= e($u['role_slug']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Sujet
                <input type="text" name="subject" required>
            </label>
            <label>Message
                <textarea name="body" required></textarea>
            </label>
            <button class="btn" type="submit">Envoyer</button>
        </form>
    </article>
    <?php endif; ?>

    <article class="panel">
        <div class="panel-header">
            <h3>Boîte de réception</h3>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Date</th><th>Expéditeur</th><th>Sujet</th><th>Message</th><th>Action</th></tr></thead>
                <tbody>
                <?php if (empty($inbox)): ?>
                    <tr><td colspan="5" class="empty-row">Aucun message recu.</td></tr>
                <?php else: ?>
                    <?php foreach ($inbox as $m): ?>
                    <tr>
                        <td><?= e($m['created_at']) ?></td>
                        <td><?= e($m['sender_name'] ?? '-') ?></td>
                        <td><strong><?= e($m['subject']) ?></strong></td>
                        <td><?= e($m['body']) ?></td>
                        <td>
                            <?php if ((int) $m['is_read'] === 0): ?>
                            <form method="post" action="<?= e(url('/messages/' . $m['id'] . '/read')) ?>">
                                <?= csrf_field() ?>
                                <button class="btn btn-outline btn-sm" type="submit">Marquer lu</button>
                            </form>
                            <?php else: ?>
                                <span class="badge badge-success">Lu</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>

<section class="panel">
    <div class="panel-header">
        <h3>Messages envoyes</h3>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Date</th><th>Destinataire</th><th>Sujet</th><th>Message</th><th>Statut</th></tr></thead>
            <tbody>
            <?php if (empty($sent)): ?>
                <tr><td colspan="5" class="empty-row">Aucun message envoye.</td></tr>
            <?php else: ?>
                <?php foreach ($sent as $m): ?>
                <tr>
                    <td><?= e($m['created_at']) ?></td>
                    <td><?= e($m['recipient_name'] ?? '-') ?></td>
                    <td><?= e($m['subject']) ?></td>
                    <td><?= e($m['body']) ?></td>
                    <td>
                        <?php if ((int) $m['is_read']): ?>
                            <span class="badge badge-success">Lu</span>
                        <?php else: ?>
                            <span class="badge">Non lu</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
