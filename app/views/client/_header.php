<?php
// Partiel : en-tête commun aux pages de l'espace client.
// Les pages client ne passent pas par le gabarit "agent" (header.php /
// footer.php du dossier layouts), elles ont leur propre habillage.
$ca = strtolower(preg_replace('/[^a-zA-Z]/', '', $_GET['action'] ?? 'index'));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titre ?? 'Espace adhérent') ?> - <?= e(APP_NAME) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container">
        <a class="navbar-brand" href="<?= url('client', 'dashboard') ?>">
            <i class="bi bi-book-half"></i> Médiathèque — espace adhérent
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#cliNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="cliNav">
            <?php if (!empty($_SESSION['client'])): ?>
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    <li class="nav-item">
                        <a class="nav-link <?= $ca === 'dashboard' ? 'active' : '' ?>"
                           href="<?= url('client', 'dashboard') ?>">
                            <i class="bi bi-speedometer2"></i> Mon espace
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $ca === 'reserver' ? 'active' : '' ?>"
                           href="<?= url('client', 'reserver') ?>">
                            <i class="bi bi-calendar-plus"></i> Réserver une salle
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i>
                            <?= e($_SESSION['client']['prenom'] . ' ' . $_SESSION['client']['nom']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <span class="dropdown-item-text small text-muted">
                                    <?= e($_SESSION['client']['email']) ?>
                                </span>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="post" action="<?= url('client', 'logout') ?>" class="px-1">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="dropdown-item">
                                        <i class="bi bi-box-arrow-right"></i> Déconnexion
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            <?php else: ?>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('client', 'login') ?>">Se connecter</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('client', 'register') ?>">Créer un compte</a>
                    </li>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</nav>

<main class="container py-4">
    <?php if (!empty($_SESSION['flash'])): ?>
        <div class="alert alert-<?= e($_SESSION['flash']['type']) ?> alert-dismissible fade show" role="alert">
            <?= e($_SESSION['flash']['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>
