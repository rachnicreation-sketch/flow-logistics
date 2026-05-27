<section class="page-header">
    <div>
        <h2>Tableau de bord ERP — LogiFlow SCM</h2>
        <p>Vue globale des opérations : logistique, commerce, transport et finances.</p>
    </div>
</section>

<!-- KPIs Opérationnels -->
<section class="kpi-grid" style="grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));">
    <article class="kpi-card">
        <span class="kpi-label">Stock global</span>
        <strong><?= number_format((float) $metrics['stock_total'], 0, ',', ' ') ?></strong>
        <span class="kpi-sub">Unités en entrepôt</span>
    </article>
    <article class="kpi-card <?= $metrics['ruptures'] > 0 ? 'warning' : 'success' ?>">
        <span class="kpi-label">Ruptures / alertes</span>
        <strong><?= (int) $metrics['ruptures'] ?></strong>
        <span class="kpi-sub">Produits sous seuil</span>
    </article>
    <article class="kpi-card">
        <span class="kpi-label">Commandes actives</span>
        <strong><?= (int) $metrics['orders_in_progress'] ?></strong>
        <span class="kpi-sub">À valider ou préparer</span>
    </article>
    <article class="kpi-card">
        <span class="kpi-label">Livraisons en cours</span>
        <strong><?= (int) $metrics['deliveries_in_progress'] ?></strong>
        <span class="kpi-sub">Suivi transport</span>
    </article>
    <article class="kpi-card success">
        <span class="kpi-label">CA encaissé</span>
        <strong><?= number_format((float) $metrics['total_revenue'], 0, ',', ' ') ?> €</strong>
        <span class="kpi-sub">Factures payées</span>
    </article>
    <article class="kpi-card <?= $metrics['unpaid_invoices'] > 0 ? 'warning' : '' ?>">
        <span class="kpi-label">Créances clients</span>
        <strong><?= number_format((float) $metrics['unpaid_invoices'], 0, ',', ' ') ?> €</strong>
        <span class="kpi-sub">Factures impayées</span>
    </article>
    <article class="kpi-card">
        <span class="kpi-label">Dépenses (30j)</span>
        <strong><?= number_format((float) $metrics['recent_expenses'], 0, ',', ' ') ?> €</strong>
        <span class="kpi-sub">Opérationnel</span>
    </article>
    <article class="kpi-card <?= $metrics['pending_returns'] > 0 ? 'warning' : '' ?>">
        <span class="kpi-label">Retours en attente</span>
        <strong><?= (int) $metrics['pending_returns'] ?></strong>
        <span class="kpi-sub">Reverse logistics</span>
    </article>
    <article class="kpi-card <?= $metrics['planned_maintenances'] > 0 ? 'warning' : 'success' ?>">
        <span class="kpi-label">Maintenances planifiées</span>
        <strong><?= (int) $metrics['planned_maintenances'] ?></strong>
        <span class="kpi-sub">Flotte véhicules</span>
    </article>
</section>

<!-- Modules ERP -->
<section class="module-grid">
    <a class="module-card" href="<?= e(url('/suppliers')) ?>">
        <span class="module-chip">01</span>
        <h3>Fournisseurs</h3>
        <p>Référentiel partenaires, contrats et historique achats.</p>
    </a>
    <a class="module-card" href="<?= e(url('/purchases')) ?>">
        <span class="module-chip">02</span>
        <h3>Achats</h3>
        <p>Bons de commande, réception marchandises et retours.</p>
    </a>
    <a class="module-card" href="<?= e(url('/stocks')) ?>">
        <span class="module-chip">03</span>
        <h3>Stocks</h3>
        <p>Mouvements FIFO/LIFO, inventaire et alertes rupture.</p>
    </a>
    <a class="module-card" href="<?= e(url('/warehouses')) ?>">
        <span class="module-chip">04</span>
        <h3>Entrepôts (WMS)</h3>
        <p>Zones, emplacements, localisation produits.</p>
    </a>
    <a class="module-card" href="<?= e(url('/orders')) ?>">
        <span class="module-chip">05</span>
        <h3>Commandes clients</h3>
        <p>Cycle complet : commande → préparation → livraison.</p>
    </a>
    <a class="module-card" href="<?= e(url('/invoices')) ?>">
        <span class="module-chip">06</span>
        <h3>Facturation</h3>
        <p>Factures TVA, paiements partiels et relances.</p>
    </a>
    <a class="module-card" href="<?= e(url('/deliveries')) ?>">
        <span class="module-chip">07</span>
        <h3>Livraisons (TMS)</h3>
        <p>Planification tournées, suivi GPS, bon de livraison.</p>
    </a>
    <a class="module-card" href="<?= e(url('/vehicles')) ?>">
        <span class="module-chip">08</span>
        <h3>Flotte véhicules</h3>
        <p>Gestion parc, disponibilité, assurances.</p>
    </a>
    <a class="module-card" href="<?= e(url('/drivers')) ?>">
        <span class="module-chip">09</span>
        <h3>Chauffeurs (RH)</h3>
        <p>Permis, heures travaillées, primes et sanctions.</p>
    </a>
    <a class="module-card" href="<?= e(url('/maintenances')) ?>">
        <span class="module-chip">10</span>
        <h3>Maintenance flotte</h3>
        <p>Entretiens planifiés, réparations et coûts SAV.</p>
    </a>
    <a class="module-card" href="<?= e(url('/parcels')) ?>">
        <span class="module-chip">11</span>
        <h3>Colis & Expéditions</h3>
        <p>Tracking, poids, dimensions et étiquettes.</p>
    </a>
    <a class="module-card" href="<?= e(url('/customs')) ?>">
        <span class="module-chip">12</span>
        <h3>Douanes</h3>
        <p>Déclarations import/export, taxes et transit.</p>
    </a>
    <a class="module-card" href="<?= e(url('/expenses')) ?>">
        <span class="module-chip">13</span>
        <h3>Dépenses</h3>
        <p>Carburant, péages, maintenance et frais généraux.</p>
    </a>
    <a class="module-card" href="<?= e(url('/returns')) ?>">
        <span class="module-chip">14</span>
        <h3>Retours logistiques</h3>
        <p>Reverse logistics : inspection, remboursement.</p>
    </a>
    <a class="module-card" href="<?= e(url('/reports')) ?>">
        <span class="module-chip">15</span>
        <h3>Rapports & Stats</h3>
        <p>Exports PDF/CSV, KPIs et audit opérationnel.</p>
    </a>
    <a class="module-card" href="<?= e(url('/tickets')) ?>">
        <span class="module-chip">16</span>
        <h3>Ticketing SAV</h3>
        <p>Incidents, attribution et résolution collaborative.</p>
    </a>
</section>

<!-- Graphiques -->
<section class="split-grid">
    <article class="panel">
        <div class="panel-header">
            <h3>Évolution des ventes (6 mois)</h3>
        </div>
        <div class="panel-body">
            <canvas id="salesChart" data-chart='<?= e(json_encode($salesByMonth, JSON_UNESCAPED_UNICODE)) ?>'></canvas>
        </div>
    </article>
    <article class="panel">
        <div class="panel-header">
            <h3>CA encaissé vs Dépenses (6 mois)</h3>
        </div>
        <div class="panel-body">
            <canvas id="revenueExpensesChart" data-chart='<?= e(json_encode($revenueVsExpenses, JSON_UNESCAPED_UNICODE)) ?>'></canvas>
        </div>
    </article>
</section>

<section class="split-grid">
    <article class="panel">
        <div class="panel-header">
            <h3>Répartition du stock par entrepôt</h3>
        </div>
        <div class="panel-body">
            <canvas id="warehouseChart" data-chart='<?= e(json_encode($stockByWarehouse, JSON_UNESCAPED_UNICODE)) ?>'></canvas>
        </div>
    </article>

    <article class="panel">
        <div class="panel-header">
            <h3>Alertes stock faible</h3>
            <a href="<?= e(url('/purchases')) ?>" class="btn btn-sm btn-blue">Générer un achat</a>
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
                    <tr><td colspan="4" class="empty-row">✅ Aucune alerte stock.</td></tr>
                <?php else: ?>
                    <?php foreach ($lowStockProducts as $p): ?>
                    <tr>
                        <td><code><?= e($p['sku']) ?></code></td>
                        <td><?= e($p['name']) ?></td>
                        <td><span class="badge badge-gray"><?= e((string)(float)$p['min_stock']) ?></span></td>
                        <td><span class="badge badge-danger"><?= e((string)(float)$p['current_stock']) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>

<section class="split-grid">
    <article class="panel">
        <div class="panel-header">
            <h3>Notifications récentes</h3>
            <a href="<?= e(url('/notifications')) ?>" class="btn btn-yellow btn-sm">Tout voir</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Type</th><th>Message</th><th>Date</th></tr>
                </thead>
                <tbody>
                <?php if (empty($notifications)): ?>
                    <tr><td colspan="3" class="empty-row">Aucune notification.</td></tr>
                <?php else: ?>
                    <?php foreach ($notifications as $n): ?>
                    <tr>
                        <td><span class="badge"><?= e($n['type']) ?></span></td>
                        <td><?= e($n['title']) ?> — <small class="muted-inline"><?= e($n['message']) ?></small></td>
                        <td class="muted-inline"><?= e(date('d/m H:i', strtotime($n['created_at']))) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </article>

    <article class="panel panel-pad">
        <h3 style="margin-bottom: 15px;">Actions rapides</h3>
        <div style="display: flex; flex-direction: column; gap: 10px;">
            <a href="<?= e(url('/orders')) ?>" class="btn btn-primary" style="text-align: center;">
                <i class="fa-solid fa-plus"></i> Nouvelle commande
            </a>
            <a href="<?= e(url('/invoices')) ?>" class="btn btn-outline" style="text-align: center;">
                <i class="fa-solid fa-file-invoice"></i> Voir les factures
            </a>
            <a href="<?= e(url('/deliveries')) ?>" class="btn btn-outline" style="text-align: center;">
                <i class="fa-solid fa-truck"></i> Planifier une livraison
            </a>
            <a href="<?= e(url('/expenses')) ?>" class="btn btn-outline" style="text-align: center;">
                <i class="fa-solid fa-receipt"></i> Saisir une dépense
            </a>
            <a href="<?= e(url('/reports')) ?>" class="btn btn-outline" style="text-align: center;">
                <i class="fa-solid fa-chart-bar"></i> Voir les rapports
            </a>
        </div>
    </article>
</section>

<script>
// Revenue vs Expenses chart
document.addEventListener('DOMContentLoaded', () => {
    const canvas = document.getElementById('revenueExpensesChart');
    if (!canvas) return;
    const raw = JSON.parse(canvas.dataset.chart || '[]');
    if (!raw.length) return;
    const labels = raw.map(r => r.month_key);
    const revenues = raw.map(r => parseFloat(r.revenue));
    const expenses = raw.map(r => parseFloat(r.expenses));
    new Chart(canvas, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                { label: 'CA encaissé (€)', data: revenues, backgroundColor: 'rgba(16,185,129,0.7)', borderRadius: 4 },
                { label: 'Dépenses (€)', data: expenses, backgroundColor: 'rgba(220,38,38,0.6)', borderRadius: 4 }
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'top' } },
            scales: { y: { beginAtZero: true } }
        }
    });
});
</script>
