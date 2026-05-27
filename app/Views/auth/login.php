<section class="auth-card">
    <h1>Connexion a Flow Logistics</h1>

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
