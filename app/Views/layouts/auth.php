<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion | LogiFlow SCM</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
</head>
<body class="auth-body">
<div class="auth-scene">
    <div class="auth-centered">
        <?php if (!empty($flash['error'])): ?>
            <?php foreach ($flash['error'] as $message): ?>
                <div class="alert alert-danger"><?= e($message) ?></div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <?= $content ?>
    </div>
</div>
</body>
</html>
