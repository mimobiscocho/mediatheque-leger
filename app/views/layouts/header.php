<?php $cc = preg_replace('/[^a-zA-Z]/', '', $_GET['ctrl'] ?? 'home'); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titre ?? 'Accueil') ?> · <?= e(APP_NAME) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark sticky-top shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="<?= url('home') ?>">
            <i class="bi bi-book-half"></i> Médiathèque
            <span class="d-none d-md-inline fw-normal opacity-75">de Bourg-la-Reine</span>
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
