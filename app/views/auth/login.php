<?php
// Page de connexion : c'est la seule page accessible sans être connecté.
// Elle n'utilise pas le gabarit commun (pas de menu tant qu'on n'est pas
// identifié), d'où le HTML complet écrit ici.
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion - <?= e(APP_NAME) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body class="login-bg d-flex align-items-center justify-content-center">

    <div class="login-card card shadow" style="width:100%; max-width:420px;">
        <div class="card-body p-4">

            <!-- En-tête de la carte : nom de l'application -->
            <div class="text-center mb-4">
                <h1 class="h4 mb-1"><i class="bi bi-book-half"></i> Médiathèque</h1>
                <p class="text-muted small mb-0">Bourg-la-Reine — espace réservé aux agents</p>
            </div>

            <?php // Message flash (ex : "identifiants incorrects" après un échec) ?>
            <?php if (!empty($_SESSION['flash'])): ?>
                <div class="alert alert-<?= e($_SESSION['flash']['type']) ?> py-2 small">
                    <?= e($_SESSION['flash']['message']) ?>
                </div>
                <?php unset($_SESSION['flash']); ?>
            <?php endif; ?>

            <!-- Formulaire de connexion : envoyé à AuthController::authenticate -->
            <form method="post" action="<?= url('auth', 'authenticate') ?>">
                <?= csrf_field() // jeton anti-CSRF obligatoire sur tout POST ?>
                <div class="mb-3">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" name="email" id="email" class="form-control" required autofocus>
                </div>
                <div class="mb-4">
                    <label class="form-label" for="mdp">Mot de passe</label>
                    <input type="password" name="mot_de_passe" id="mdp" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-mediatheque w-100">Se connecter</button>
            </form>

            <!-- Comptes fournis avec le jeu de données de démonstration -->
            <div class="alert alert-light border mt-4 mb-0 small">
                <div class="fw-bold mb-1">Comptes de démonstration</div>
                <div>admin@mediatheque.fr / admin123</div>
                <div>agent@mediatheque.fr / agent123</div>
            </div>
        </div>
    </div>

</body>
</html>
