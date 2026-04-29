<section class="page-header">
    <div class="header-left">
        <h2>Planning des Livraisons</h2>
        <p>Vue chronologique des livraisons planifiées et effectuées.</p>
    </div>
    <div class="header-right">
        <a href="<?= e(url('/deliveries')) ?>" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Nouvelle Livraison
        </a>
    </div>
</section>

<div class="planning-container">
    <?php if (empty($groupedDeliveries)): ?>
        <section class="panel panel-pad text-center">
            <p>Aucune livraison planifiée pour le moment.</p>
        </section>
    <?php else: ?>
        <?php foreach ($groupedDeliveries as $date => $deliveries): ?>
            <div class="planning-day">
                <div class="day-header">
                    <h3><?= date('l d F Y', strtotime($date)) ?></h3>
                    <span class="badge badge-outline"><?= count($deliveries) ?> livraison(s)</span>
                </div>
                <div class="day-content">
                    <?php foreach ($deliveries as $d): ?>
                        <div class="delivery-card status-<?= $d['status'] ?>">
                            <div class="card-time">
                                <?= date('H:i', strtotime($d['planned_date'] ?? $d['created_at'])) ?>
                            </div>
                            <div class="card-main">
                                <strong><?= e($d['order_ref']) ?></strong> - <?= e($d['customer_name']) ?>
                                <p class="text-muted small">
                                    <i class="fa-solid fa-truck"></i> <?= e($d['plate_number'] ?? 'Non assigné') ?> | 
                                    <i class="fa-solid fa-user-tie"></i> <?= e($d['driver_name'] ?? 'Non assigné') ?>
                                </p>
                            </div>
                            <div class="card-status">
                                <span class="badge badge-<?= $d['status'] === 'delivered' ? 'success' : ($d['status'] === 'in_transit' ? 'primary' : 'warning') ?>">
                                    <?= e($d['status']) ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<style>
.planning-container {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}
.planning-day {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    overflow: hidden;
}
.day-header {
    background: #f8fafc;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.day-header h3 {
    margin: 0;
    font-size: 1.1rem;
    color: var(--primary);
    text-transform: capitalize;
}
.day-content {
    padding: 1rem;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1rem;
}
.delivery-card {
    border: 1px solid #e2e8f0;
    border-left: 4px solid #cbd5e1;
    border-radius: 6px;
    padding: 1rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: transform 0.2s;
}
.delivery-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
}
.delivery-card.status-delivered { border-left-color: #10b981; }
.delivery-card.status-in_transit { border-left-color: #3b82f6; }
.delivery-card.status-pending { border-left-color: #f59e0b; }
.delivery-card.status-failed { border-left-color: #ef4444; }

.card-time {
    font-weight: 700;
    font-size: 1.2rem;
    color: var(--primary);
    min-width: 60px;
}
.card-main {
    flex: 1;
}
.card-main p {
    margin: 0.25rem 0 0;
}
</style>
