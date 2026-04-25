<section class="page-header">
    <div>
        <h2>Tableau de bord SCM</h2>
        <p>Vue globale des operations logistiques, commerciales et transport.</p>
    </div>
</section>

<section class="kpi-grid">
    <article class="kpi-card">
        <span class="kpi-label">Stock global</span>
        <strong><?= number_format((float) $metrics['stock_total'], 2, ',', ' ') ?></strong>
        <span class="kpi-sub">Unités en entrepot</span>
    </article>
    <article class="kpi-card <?= $metrics['ruptures'] > 0 ? 'warning' : 'success' ?>">
        <span class="kpi-label">Rupture / alerte</span>
        <strong><?= (int) $metrics['ruptures'] ?></strong>
        <span class="kpi-sub">Produits sous le seuil</span>
    </article>
    <article class="kpi-card">
        <span class="kpi-label">Commandes actives</span>
        <strong><?= (int) $metrics['orders_in_progress'] ?></strong>
        <span class="kpi-sub">A valider ou preparer</span>
    </article>
    <article class="kpi-card">
        <span class="kpi-label">Livraisons actives</span>
        <strong><?= (int) $metrics['deliveries_in_progress'] ?></strong>
        <span class="kpi-sub">Suivi transport</span>
    </article>
</section>

<section class="module-grid">
    <a class="module-card" href="<?= e(url('/suppliers')) ?>">
        <span class="module-chip">01</span>
        <h3>Fournisseurs</h3>
        <p>Référentiel partenaires et historique achats.</p>
    </a>
    <a class="module-card" href="<?= e(url('/purchases')) ?>">
        <span class="module-chip">02</span>
        <h3>Achats</h3>
        <p>Creation des bons et réception marchandises.</p>
    </a>
    <a class="module-card" href="<?= e(url('/stocks')) ?>">
        <span class="module-chip">03</span>
        <h3>Stocks</h3>
        <p>Mouvements FIFO/LIFO et disponibilite temps réel.</p>
    </a>
    <a class="module-card" href="<?= e(url('/orders')) ?>">
        <span class="module-chip">04</span>
        <h3>Commandes</h3>
        <p>Cycle client de la commande a la facture.</p>
    </a>
    <a class="module-card" href="<?= e(url('/deliveries')) ?>">
        <span class="module-chip">05</span>
        <h3>Livraisons</h3>
        <p>Planification transport et suivi chauffeur.</p>
    </a>
    <a class="module-card" href="<?= e(url('/reports')) ?>">
        <span class="module-chip">06</span>
        <h3>Rapports</h3>
        <p>Exports PDF, audit et pilotage operationnel.</p>
    </a>
    <a class="module-card" href="<?= e(url('/tickets')) ?>">
        <span class="module-chip">07</span>
        <h3>Ticketing</h3>
        <p>Suivi des incidents, attribution et resolution collaborative.</p>
    </a>
</section>

<section class="split-grid">
    <article class="panel">
        <div class="panel-header">
            <h3>Evolution des ventes (6 mois)</h3>
        </div>
        <div class="panel-body">
            <canvas id="salesChart" data-chart='<?= e(json_encode($salesByMonth, JSON_UNESCAPED_UNICODE)) ?>'></canvas>
        </div>
    </article>
    <article class="panel">
        <div class="panel-header">
            <h3>Repartition du stock par entrepot</h3>
        </div>
        <div class="panel-body">
            <canvas id="warehouseChart" data-chart='<?= e(json_encode($stockByWarehouse, JSON_UNESCAPED_UNICODE)) ?>'></canvas>
        </div>
    </article>
</section>

<section class="split-grid">
    <article class="panel">
        <div class="panel-header">
            <h3>Alertes stock faible</h3>
            <a href="<?= e(url('/purchases')) ?>" class="btn btn-sm">Generer un achat</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>SKU</th>
                        <th>Produit</th>
                        <th>Seuil min</th>
                        <th>Stock actuel</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($lowStockProducts)): ?>
                    <tr><td colspan="4" class="empty-row">Aucune alerte de stock.</td></tr>
                <?php else: ?>
                    <?php foreach ($lowStockProducts as $p): ?>
                    <tr>
                        <td><code><?= e($p['sku']) ?></code></td>
                        <td><?= e($p['name']) ?></td>
                        <td><span class="badge badge-gray"><?= e((string) (float) $p['min_stock']) ?> <?= e($p['unit']) ?></span></td>
                        <td><span class="badge badge-danger"><?= e((string) (float) $p['current_stock']) ?> <?= e($p['unit']) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </article>

    <article class="panel">
        <div class="panel-header">
            <h3>Notifications recentes</h3>
            <a href="<?= e(url('/notifications')) ?>" class="btn btn-outline btn-sm">Tout voir</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Message</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($notifications)): ?>
                    <tr><td colspan="3" class="empty-row">Aucune notification.</td></tr>
                <?php else: ?>
                    <?php foreach ($notifications as $n): ?>
                    <tr>
                        <td><span class="badge"><?= e($n['type']) ?></span></td>
                        <td><?= e($n['title']) ?> - <small class="muted-inline"><?= e($n['message']) ?></small></td>
                        <td class="muted-inline"><?= e(date('d/m H:i', strtotime($n['created_at']))) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>
