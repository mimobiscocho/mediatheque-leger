<?php
// Page de connexion de l'espace client (adhérent).
// Standalone : pas de gabarit "agent". Mise en page reprenant celle de
// /auth/login pour rester cohérent visuellement.
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Espace adhérent - <?= e(APP_NAME) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body class="login-bg d-flex align-items-center justify-content-center">

    <div class="login-card card shadow" style="width:100%; max-width:420px;">
        <div class="card-body p-4">

            <div class="text-center mb-4">
                <h1 class="h4 mb-1"><i class="bi bi-person-badge"></i> Espace adhérent</h1>
                <p class="text-muted small mb-0">Connectez-vous pour réserver une salle</p>
            </div>

            <?php if (!empty($_SESSION['flash'])): ?>
                <div class="alert alert-<?= e($_SESSION['flash']['type']) ?> py-2 small">
                    <?= e($_SESSION['flash']['message']) ?>
                </div>
                <?php unset($_SESSION['flash']); ?>
            <?php endif; ?>

            <form method="post" action="<?= url('client', 'authenticate') ?>">
                <?= csrf_field() ?>
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

            <div class="text-center mt-3 small">
                Pas encore de compte ?
                <a href="<?= url('client', 'register') ?>">Créer un compte</a>
            </div>

            <div class="text-center mt-3 small">
                <a href="<?= url('auth', 'login') ?>" class="text-muted">
                    <i class="bi bi-shield-lock"></i> Accès agents
                </a>
            </div>
        </div>
    </div>

</body>
</html>
