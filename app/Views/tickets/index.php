<?php

use App\Core\Auth;

$currentUserId = (int) (Auth::id() ?? 0);
?>
<section class="page-header">
    <div>
        <h2>Module Ticketing</h2>
        <p>Support interne, suivi des incidents et coordination inter-modules.</p>
    </div>
    <div class="action-row">
        <a class="btn btn-outline" href="<?= e(url('/dashboard')) ?>">Retour dashboard</a>
    </div>
</section>

<section class="kpi-grid">
    <article class="kpi-card">
        <span class="kpi-label">Ouverts</span>
        <strong><?= (int) ($stats['open'] ?? 0) ?></strong>
        <span class="kpi-sub">A traiter</span>
    </article>
    <article class="kpi-card warning">
        <span class="kpi-label">En cours</span>
        <strong><?= (int) ($stats['in_progress'] ?? 0) ?></strong>
        <span class="kpi-sub">Interventions actives</span>
    </article>
    <article class="kpi-card">
        <span class="kpi-label">Resolus</span>
        <strong><?= (int) ($stats['resolved'] ?? 0) ?></strong>
        <span class="kpi-sub">En attente cloture</span>
    </article>
    <article class="kpi-card success">
        <span class="kpi-label">Clotures</span>
        <strong><?= (int) ($stats['closed'] ?? 0) ?></strong>
        <span class="kpi-sub">Historique finalise</span>
    </article>
</section>

<section class="split-grid">
    <article class="panel panel-pad">
        <h3>Nouveau ticket</h3>
        <form method="post" action="<?= e(url('/tickets')) ?>" class="grid-form">
            <?= csrf_field() ?>

            <?php if (!empty($isSuper)): ?>
            <label>Entreprise
                <select name="company_id">
                    <?php foreach ($companies as $company): ?>
                        <option value="<?= (int) $company['id'] ?>"><?= e($company['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <?php endif; ?>

            <label>Titre
                <input type="text" name="title" required>
            </label>

            <label>Module concerne
                <select name="module_name">
                    <option value="dashboard">Dashboard</option>
                    <option value="suppliers">Fournisseurs</option>
                    <option value="purchases">Achats</option>
                    <option value="products">Produits</option>
                    <option value="stocks">Stocks</option>
                    <option value="orders">Commandes</option>
                    <option value="deliveries">Livraisons</option>
                    <option value="reports">Rapports</option>
                    <option value="settings">Paramètres</option>
                    <option value="other">Autre</option>
                </select>
            </label>

            <label>Priorité
                <select name="priority">
                    <option value="low">Basse</option>
                    <option value="medium" selected>Moyenne</option>
                    <option value="high">Haute</option>
                    <option value="urgent">Urgente</option>
                </select>
            </label>

            <label>Assignér a
                <select name="assignéd_to">
                    <option value="">Non assigné</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= (int) $user['id'] ?>"><?= e($user['name']) ?> (<?= e($user['role_slug']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>Échéance
                <input type="datetime-local" name="due_at">
            </label>

            <label>Description
                <textarea name="description" required></textarea>
            </label>

            <button class="btn" type="submit">Créer ticket</button>
        </form>
    </article>

    <article class="panel panel-pad">
        <h3>Bonnes pratiques</h3>
        <ul class="compact-list">
            <li>Utilisez un titre precis pour faciliter la priorisation.</li>
            <li>Affectez chaque ticket a un responsable clair.</li>
            <li>Mettez le statut à jour apres chaque action.</li>
            <li>Documentez les actions dans les commentaires.</li>
            <li>Fermez le ticket seulement apres verification.</li>
        </ul>
    </article>
</section>

<section class="panel">
    <div class="panel-header">
        <h3>Tickets en cours</h3>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>Numero</th>
                <th>Titre</th>
                <th>Module</th>
                <th>Statut</th>
                <th>Priorité</th>
                <th>Assigné</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($tickets)): ?>
                <tr>
                    <td colspan="7" class="empty-row">Aucun ticket pour le moment.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($tickets as $ticket): ?>
                    <?php $ticketId = (int) $ticket['id']; ?>
                    <tr>
                        <td><a href="#ticket-<?= $ticketId ?>"><strong><?= e($ticket['ticket_number']) ?></strong></a></td>
                        <td><?= e($ticket['title']) ?></td>
                        <td><?= e($ticket['module_name'] ?? '-') ?></td>
                        <td><span class="badge"><?= e($ticket['status']) ?></span></td>
                        <td><span class="badge"><?= e($ticket['priority']) ?></span></td>
                        <td><?= e($ticket['assignée_name'] ?? 'Non assigné') ?></td>
                        <td class="action-grid">
                            <form method="post" action="<?= e(url('/tickets/' . $ticketId . '/assign')) ?>" class="inline-form">
                                <?= csrf_field() ?>
                                <select name="assignéd_to">
                                    <option value="">Non assigné</option>
                                    <?php foreach ($users as $user): ?>
                                        <option value="<?= (int) $user['id'] ?>" <?= (int) ($ticket['assignéd_to'] ?? 0) === (int) $user['id'] ? 'selected' : '' ?>>
                                            <?= e($user['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="btn btn-outline btn-sm" type="submit">Assignér</button>
                            </form>

                            <form method="post" action="<?= e(url('/tickets/' . $ticketId . '/assign-self')) ?>">
                                <?= csrf_field() ?>
                                <button class="btn btn-outline btn-sm" type="submit">M'assignér</button>
                            </form>

                            <form method="post" action="<?= e(url('/tickets/' . $ticketId . '/status')) ?>" class="inline-form">
                                <?= csrf_field() ?>
                                <select name="status">
                                    <option value="open" <?= $ticket['status'] === 'open' ? 'selected' : '' ?>>open</option>
                                    <option value="in_progress" <?= $ticket['status'] === 'in_progress' ? 'selected' : '' ?>>in_progress</option>
                                    <option value="resolved" <?= $ticket['status'] === 'resolved' ? 'selected' : '' ?>>resolved</option>
                                    <option value="closed" <?= $ticket['status'] === 'closed' ? 'selected' : '' ?>>closed</option>
                                </select>
                                <button class="btn btn-outline btn-sm" type="submit">Statut</button>
                            </form>

                            <form method="post" action="<?= e(url('/tickets/' . $ticketId . '/priority')) ?>" class="inline-form">
                                <?= csrf_field() ?>
                                <select name="priority">
                                    <option value="low" <?= $ticket['priority'] === 'low' ? 'selected' : '' ?>>low</option>
                                    <option value="medium" <?= $ticket['priority'] === 'medium' ? 'selected' : '' ?>>medium</option>
                                    <option value="high" <?= $ticket['priority'] === 'high' ? 'selected' : '' ?>>high</option>
                                    <option value="urgent" <?= $ticket['priority'] === 'urgent' ? 'selected' : '' ?>>urgent</option>
                                </select>
                                <button class="btn btn-outline btn-sm" type="submit">Priorité</button>
                            </form>

                            <?php if ($ticket['status'] !== 'closed'): ?>
                                <form method="post" action="<?= e(url('/tickets/' . $ticketId . '/close')) ?>">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-outline btn-sm" type="submit">Clore</button>
                                </form>
                            <?php else: ?>
                                <form method="post" action="<?= e(url('/tickets/' . $ticketId . '/reopen')) ?>">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-outline btn-sm" type="submit">Reouvrir</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php if (!empty($tickets)): ?>
<section class="ticket-stack">
    <?php foreach ($tickets as $ticket): ?>
        <?php
        $ticketId = (int) $ticket['id'];
        $comments = $commentsByTicket[$ticketId] ?? [];
        ?>
        <article id="ticket-<?= $ticketId ?>" class="panel panel-pad ticket-card">
            <div class="ticket-head">
                <h3><?= e($ticket['ticket_number']) ?> - <?= e($ticket['title']) ?></h3>
                <div class="action-row">
                    <span class="badge"><?= e($ticket['status']) ?></span>
                    <span class="badge"><?= e($ticket['priority']) ?></span>
                </div>
            </div>
            <p class="muted-text"><?= nl2br(e($ticket['description'])) ?></p>

            <div class="ticket-meta-grid">
                <div><strong>Reporter:</strong> <?= e($ticket['reporter_name'] ?? '-') ?></div>
                <div><strong>Assigné:</strong> <?= e($ticket['assignée_name'] ?? '-') ?></div>
                <div><strong>Créé le:</strong> <?= e($ticket['created_at']) ?></div>
                <div><strong>Échéance:</strong> <?= e($ticket['due_at'] ?? '-') ?></div>
            </div>

            <h4>Commentaires</h4>
            <?php if (empty($comments)): ?>
                <p class="muted-text">Aucun commentaire pour ce ticket.</p>
            <?php else: ?>
                <ul class="compact-list ticket-comments">
                    <?php foreach ($comments as $comment): ?>
                        <li>
                            <strong><?= e($comment['user_name'] ?? 'Systeme') ?>:</strong>
                            <?= e($comment['comment']) ?>
                            <span class="muted-inline">(<?= e($comment['created_at']) ?>)</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <form method="post" action="<?= e(url('/tickets/' . $ticketId . '/comment')) ?>" class="grid-form">
                <?= csrf_field() ?>
                <label>Ajouter un commentaire
                    <textarea name="comment" required></textarea>
                </label>
                <button class="btn btn-outline" type="submit">Ajouter commentaire</button>
            </form>

            <?php if ((int) ($ticket['assignéd_to'] ?? 0) !== $currentUserId): ?>
                <form method="post" action="<?= e(url('/tickets/' . $ticketId . '/assign-self')) ?>">
                    <?= csrf_field() ?>
                    <button class="btn btn-outline" type="submit">Prendre ce ticket</button>
                </form>
            <?php endif; ?>
        </article>
    <?php endforeach; ?>
</section>
<?php endif; ?>
