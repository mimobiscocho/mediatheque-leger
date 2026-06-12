<?php
// Gabarit commun : début de page (ouvert ici, fermé dans footer.php).
// $cc = nom du contrôleur courant, sert à surligner le bon lien du menu.
$cc = strtolower(preg_replace('/[^a-zA-Z]/', '', $_GET['ctrl'] ?? 'home'));

// Petite fonction locale : renvoie "active" si le lien correspond
// à la page en cours, pour le mettre en évidence dans le menu.
function navActive(string $ctrl, string $courant): string
{
    return $ctrl === $courant ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Le titre de l'onglet = titre de la page + nom de l'application -->
    <title><?= e($titre ?? 'Accueil') ?> - <?= e(APP_NAME) ?></title>
    <!-- Bootstrap 5 et ses icônes (via CDN), puis notre feuille de style -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>

<!-- ===================== Barre de navigation ===================== -->
<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container-fluid">
        <!-- Logo / nom de l'application : ramène au tableau de bord -->
        <a class="navbar-brand" href="<?= url('home') ?>">
            <i class="bi bi-book-half"></i> Médiathèque de Bourg-la-Reine
        </a>

        <!-- Bouton "hamburger" affiché sur mobile -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <!-- Un lien par module de l'application -->
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link <?= navActive('home', $cc) ?>" href="<?= url('home') ?>">Tableau de bord</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= navActive('adherent', $cc) ?>" href="<?= url('adherent') ?>">Adhérents</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= navActive('abonnement', $cc) ?>" href="<?= url('abonnement') ?>">Abonnements</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= navActive('livre', $cc) ?>" href="<?= url('livre') ?>">Livres</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= navActive('materiel', $cc) ?>" href="<?= url('materiel') ?>">Matériels</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= navActive('salle', $cc) ?>" href="<?= url('salle') ?>">Salles</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= navActive('pret', $cc) ?>" href="<?= url('pret') ?>">Prêts</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= navActive('reservation', $cc) ?>" href="<?= url('reservation') ?>">Réservations</a>
                </li>

                <?php if (!empty($_SESSION['agent'])): ?>
                    <!-- Menu de l'agent connecté (profil + déconnexion) -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i>
                            <?= e($_SESSION['agent']['prenom'] . ' ' . $_SESSION['agent']['nom']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <!-- Rappel du compte connecté et de son rôle -->
                                <span class="dropdown-item-text small text-muted">
                                    <?= e($_SESSION['agent']['email']) ?><br>
                                    Rôle : <?= e($_SESSION['agent']['role']) ?>
                                </span>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="<?= url('profil') ?>">
                                    <i class="bi bi-person"></i> Mon profil
                                </a>
                            </li>
                            <li>
                                <!-- La déconnexion passe par un POST + jeton CSRF
                                     (pas un simple lien, pour éviter les déconnexions forcées) -->
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

<!-- ===================== Contenu de la page ===================== -->
<main class="container py-4">
    <?php // Affichage du message "flash" éventuel (succès ou erreur),
          // déposé en session par le contrôleur précédent puis effacé. ?>
    <?php if (!empty($_SESSION['flash'])): ?>
        <div class="alert alert-<?= e($_SESSION['flash']['type']) ?> alert-dismissible fade show" role="alert">
            <?= e($_SESSION['flash']['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>
