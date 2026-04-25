<section class="page-header">
    <h2>Paramètres entreprise</h2>
    <p>Personnalisation SaaS par société.</p>
</section>

<section class="panel">
    <h3>Configuration</h3>
    <form method="post" action="<?= e(url('/settings')) ?>" class="grid-form">
        <?= csrf_field() ?>
        <label>Fuseau horaire
            <input type="text" name="company_timezone" value="<?= e($settings['company_timezone'] ?? 'Europe/Paris') ?>">
        </label>
        <label>Devise par défaut
            <input type="text" name="default_currency" value="<?= e($settings['default_currency'] ?? 'EUR') ?>">
        </label>
        <label>Email alertes SMTP
            <input type="email" name="smtp_alert_email" value="<?= e($settings['smtp_alert_email'] ?? '') ?>">
        </label>
        <label>Seuil alerte stock
            <input type="number" step="0.01" name="stock_alert_threshold" value="<?= e($settings['stock_alert_threshold'] ?? '1') ?>">
        </label>
        <button class="btn">Sauvegarder paramètres</button>
    </form>
</section>

