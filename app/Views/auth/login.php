<section class="auth-card">
    <h1>Connexion a Flow Logistics</h1>
    <p>Pilotez vos operations supply chain depuis une interface unifiee.</p>

    <form method="post" action="<?= e(url('/login')) ?>" class="grid-form">
        <?= csrf_field() ?>
        <label>
            Email
            <input type="email" name="email" required value="<?= e(old('email')) ?>">
        </label>
        <label>
            Mot de passe
            <input type="password" name="password" required>
        </label>
        <button class="btn" type="submit">Se connecter</button>
    </form>

    <div class="hint">
        <p><strong>Comptes de demo (mot de passe: password)</strong></p>
        <p>Super admin: <code>superadmin@flow-logistics.com</code></p>
        <p>Admin entreprise: <code>admin@flow-logistics.com</code></p>
        <p>DG: <code>dg@flow-logistics.com</code></p>
        <p>Responsable logistique: <code>logistique@flow-logistics.com</code></p>
        <p>Magasinier: <code>magasinier@flow-logistics.com</code></p>
        <p>Chauffeur: <code>chauffeur@flow-logistics.com</code></p>
    </div>
</section>
