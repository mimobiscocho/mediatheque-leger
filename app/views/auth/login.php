<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion · <?= e(APP_NAME) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body class="login-bg d-flex align-items-center justify-content-center">
    <div class="login-card card" style="width:100%; max-width:430px;">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <div class="login-brand">
                    <i class="bi bi-book-half"></i>
                </div>
                <h1 class="h4 mt-2 mb-1 fw-semibold">Médiathèque</h1>
                <p class="text-muted small mb-0">de Bourg-la-Reine — espace agents</p>
            </div>

            <?php if (!empty($_SESSION['flash'])): ?>
                <div class="alert alert-<?= e($_SESSION['flash']['type']) ?> py-2 small">
                    <?= e($_SESSION['flash']['message']) ?>
                </div>
                <?php unset($_SESSION['flash']); ?>
            <?php endif; ?>

            <form method="post" action="<?= url('auth', 'authenticate') ?>">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="email" class="form-control" required autofocus
                               placeholder="prenom@mediatheque.fr">
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label">Mot de passe</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" name="mot_de_passe" class="form-control" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-mediatheque w-100 py-2">
                    <i class="bi bi-box-arrow-in-right"></i> Se connecter
                </button>
            </form>

            <div class="alert alert-light mt-4 mb-0 small">
                <div class="fw-semibold mb-1">Comptes de démonstration</div>
                <div><i class="bi bi-person-badge text-muted"></i> admin@mediatheque.fr / admin123</div>
                <div><i class="bi bi-person text-muted"></i> agent@mediatheque.fr / agent123</div>
            </div>
        </div>
    </div>
</body>
</html>
