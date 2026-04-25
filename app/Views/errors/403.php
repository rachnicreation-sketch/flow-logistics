<main style="padding:2rem;font-family:Manrope,sans-serif">
    <h1>403 - Accès interdit</h1>
    <p>Permission manquante: <strong><?= e($permission ?? 'inconnue') ?></strong>.</p>
    <a href="<?= e(url('/dashboard')) ?>">Retour dashboard</a>
</main>

