<?php // Formulaire d'inscription d'un nouveau client. ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Créer un compte - <?= e(APP_NAME) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body class="login-bg d-flex align-items-center justify-content-center py-4">

    <div class="login-card card shadow my-4" style="width:100%; max-width:520px;">
        <div class="card-body p-4">

            <div class="text-center mb-4">
                <h1 class="h4 mb-1"><i class="bi bi-person-plus"></i> Créer un compte adhérent</h1>
                <p class="text-muted small mb-0">Inscrivez-vous pour réserver une salle en ligne</p>
            </div>

            <?php if (!empty($_SESSION['flash'])): ?>
                <div class="alert alert-<?= e($_SESSION['flash']['type']) ?> py-2 small">
                    <?= e($_SESSION['flash']['message']) ?>
                </div>
                <?php unset($_SESSION['flash']); ?>
            <?php endif; ?>

            <form method="post" action="<?= url('client', 'create') ?>" class="row g-3">
                <?= csrf_field() ?>

                <div class="col-md-6">
                    <label class="form-label" for="prenom">Prénom <span class="text-danger">*</span></label>
                    <input type="text" name="prenom" id="prenom" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="nom">Nom <span class="text-danger">*</span></label>
                    <input type="text" name="nom" id="nom" class="form-control" required>
                </div>

                <div class="col-12">
                    <label class="form-label" for="email">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" id="email" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="telephone">Téléphone</label>
                    <input type="tel" name="telephone" id="telephone" class="form-control"
                           placeholder="06 12 34 56 78">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="adresse">Adresse</label>
                    <input type="text" name="adresse" id="adresse" class="form-control">
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="mdp">Mot de passe <span class="text-danger">*</span></label>
                    <input type="password" name="mot_de_passe" id="mdp" class="form-control"
                           minlength="8" required>
                    <div class="form-text">8 caractères minimum.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="mdp2">Confirmation <span class="text-danger">*</span></label>
                    <input type="password" name="mot_de_passe_confirm" id="mdp2" class="form-control"
                           minlength="8" required>
                </div>

                <div class="col-12 d-grid">
                    <button type="submit" class="btn btn-mediatheque">
                        <i class="bi bi-check-lg"></i> Créer mon compte
                    </button>
                </div>
            </form>

            <div class="text-center mt-3 small">
                Déjà inscrit ? <a href="<?= url('client', 'login') ?>">Se connecter</a>
            </div>
        </div>
    </div>

</body>
</html>
