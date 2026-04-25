<section class="auth-card">
    <h1>Connexion a Flow Logistics</h1>
    <p>Accedez au cockpit logistique pour superviser vos donnees, vos flux et vos equipes.</p>

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

</section>
