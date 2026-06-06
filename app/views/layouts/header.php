<?php $cc = strtolower(preg_replace('/[^a-zA-Z]/', '', $_GET['ctrl'] ?? 'home')); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titre ?? 'Accueil') ?> · <?= e(APP_NAME) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= url('home') ?>">
            <i class="bi bi-book-half"></i> Médiathèque
            <span class="d-none d-md-inline opacity-75">de Bourg-la-Reine</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto">
                <?php
                $nav = [
                    'home'        => ['Tableau de bord', 'bi-speedometer2'],
                    'adherent'    => ['Adhérents',       'bi-people'],
                    'livre'       => ['Livres',          'bi-book'],
                    'materiel'    => ['Matériels',       'bi-tools'],
                    'salle'       => ['Salles',          'bi-door-open'],
                    'pret'        => ['Prêts',           'bi-arrow-left-right'],
                    'reservation' => ['Réservations',    'bi-calendar-check'],
                ];
                foreach ($nav as $key => [$label, $icon]): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $cc === $key ? 'active' : '' ?>" href="<?= url($key) ?>">
                            <i class="bi <?= $icon ?>"></i> <?= $label ?>
                        </a>
                    </li>
                <?php endforeach; ?>

                <?php if (!empty($_SESSION['agent'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i>
                            <?= e($_SESSION['agent']['prenom'] . ' ' . $_SESSION['agent']['nom']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <span class="dropdown-item-text small text-muted">
                                    <?= e($_SESSION['agent']['email']) ?><br>
                                    <span class="badge text-bg-secondary"><?= e($_SESSION['agent']['role']) ?></span>
                                </span>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="post" action="<?= url('auth', 'logout') ?>" class="px-1">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="dropdown-item">
                                        <i class="bi bi-box-arrow-right"></i> Déconnexion
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
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
