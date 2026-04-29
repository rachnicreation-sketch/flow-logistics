<section class="page-header">
    <h2>Paramètres entreprise</h2>
    <p>Personnalisation SaaS par société.</p>
</section>

<section class="panel">
    <h3>Configuration</h3>
    <form method="post" action="<?= e(url('/settings')) ?>" class="grid-form">
        <?= csrf_field() ?>
        <div class="menu-group">Général</div>
        <div class="grid-form-2">
            <label>Fuseau horaire
                <input type="text" name="company_timezone" value="<?= e($settings['company_timezone'] ?? 'Europe/Paris') ?>">
            </label>
            <label>Devise par défaut
                <input type="text" name="default_currency" value="<?= e($settings['default_currency'] ?? 'EUR') ?>">
            </label>
            <label>Langue
                <select name="app_language">
                    <option value="fr" <?= ($settings['app_language'] ?? 'fr') === 'fr' ? 'selected' : '' ?>>Français</option>
                    <option value="en" <?= ($settings['app_language'] ?? 'fr') === 'en' ? 'selected' : '' ?>>English</option>
                </select>
            </label>
            <label>Seuil alerte stock
                <input type="number" step="0.01" name="stock_alert_threshold" value="<?= e($settings['stock_alert_threshold'] ?? '1') ?>">
            </label>
        </div>

        <div class="menu-group mt-6">Configuration SMTP</div>
        <div class="grid-form-3">
            <label>Hôte SMTP
                <input type="text" name="smtp_host" value="<?= e($settings['smtp_host'] ?? '') ?>" placeholder="smtp.mailtrap.io">
            </label>
            <label>Port SMTP
                <input type="number" name="smtp_port" value="<?= e($settings['smtp_port'] ?? '587') ?>">
            </label>
            <label>Utilisateur SMTP
                <input type="text" name="smtp_user" value="<?= e($settings['smtp_user'] ?? '') ?>">
            </label>
            <label>Mot de passe SMTP
                <input type="password" name="smtp_pass" value="<?= e($settings['smtp_pass'] ?? '') ?>">
            </label>
            <label>Chiffrement
                <select name="smtp_encryption">
                    <option value="tls" <?= ($settings['smtp_encryption'] ?? 'tls') === 'tls' ? 'selected' : '' ?>>TLS</option>
                    <option value="ssl" <?= ($settings['smtp_encryption'] ?? 'tls') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                    <option value="none" <?= ($settings['smtp_encryption'] ?? 'tls') === 'none' ? 'selected' : '' ?>>Aucun</option>
                </select>
            </label>
            <label>Email expéditeur
                <input type="email" name="smtp_alert_email" value="<?= e($settings['smtp_alert_email'] ?? '') ?>" placeholder="noreply@flow-logistics.com">
            </label>
        </div>
        
        <div class="mt-6">
            <button class="btn btn-primary">Sauvegarder tous les paramètres</button>
        </div>
    </form>
</section>

<style>
.mt-6 { margin-top: 1.5rem; }
</style>
    </form>
</section>

