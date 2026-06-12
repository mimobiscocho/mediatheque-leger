<?php
// Tableau de bord : page d'accueil après connexion.
// Le contrôleur (HomeController) fournit :
//   $stats        -> compteurs adhérents / livres / matériels / salles
//   $pretsEnCours -> nombre de prêts non rendus
//   $resaActives  -> nombre de réservations confirmées à venir
//   $retards      -> liste des prêts en retard
//   $prets        -> les 5 derniers prêts
//   $reservations -> les 5 dernières réservations
?>
<h1 class="h3 mb-4 page-title"><i class="bi bi-speedometer2"></i> Tableau de bord</h1>

<!-- ===== Les 4 compteurs (chaque carte est cliquable vers son module) ===== -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <a href="<?= url('adherent') ?>" class="text-decoration-none text-reset">
            <div class="card card-stat shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <i class="bi bi-people fs-1 me-3"></i>
                    <div>
                        <div class="fs-3 fw-bold"><?= (int) $stats['adherents'] ?></div>
                        <div class="text-muted small">Adhérents</div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-lg-3">
        <a href="<?= url('livre') ?>" class="text-decoration-none text-reset">
            <div class="card card-stat shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <i class="bi bi-book fs-1 me-3"></i>
                    <div>
                        <div class="fs-3 fw-bold"><?= (int) $stats['livres'] ?></div>
                        <div class="text-muted small">Livres</div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-lg-3">
        <a href="<?= url('materiel') ?>" class="text-decoration-none text-reset">
            <div class="card card-stat shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <i class="bi bi-tools fs-1 me-3"></i>
                    <div>
                        <div class="fs-3 fw-bold"><?= (int) $stats['materiels'] ?></div>
                        <div class="text-muted small">Matériels</div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-lg-3">
        <a href="<?= url('salle') ?>" class="text-decoration-none text-reset">
            <div class="card card-stat shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <i class="bi bi-door-open fs-1 me-3"></i>
                    <div>
                        <div class="fs-3 fw-bold"><?= (int) $stats['salles'] ?></div>
                        <div class="text-muted small">Salles</div>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- ===== Activité en cours : prêts et réservations ===== -->
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="fs-1 fw-bold"><?= (int) $pretsEnCours ?></div>
                    <div class="text-muted">Prêt(s) en cours</div>
                </div>
                <a href="<?= url('pret') ?>" class="btn btn-mediatheque btn-sm">Gérer</a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="fs-1 fw-bold"><?= (int) $resaActives ?></div>
                    <div class="text-muted">Réservation(s) à venir</div>
                </div>
                <a href="<?= url('reservation') ?>" class="btn btn-mediatheque btn-sm">Gérer</a>
            </div>
        </div>
    </div>
</div>

<?php // Encadré rouge affiché uniquement s'il existe des prêts en retard ?>
<?php if (!empty($retards)): ?>
    <div class="card shadow-sm mb-4 border-danger">
        <div class="card-header bg-danger text-white">
            <i class="bi bi-exclamation-triangle"></i>
            Prêts en retard (<?= count($retards) ?>)
        </div>
        <div class="table-responsive">
            <table class="table table-sm mb-0 align-middle">
                <thead>
                    <tr><th>Adhérent</th><th>Produit</th><th>Retour prévu</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($retards as $r): ?>
                        <tr>
                            <td><?= e($r['adherent_nom']) ?></td>
                            <?php // Un prêt porte SOIT sur un livre, SOIT sur un matériel ?>
                            <td><?= e($r['livre_titre'] ?? $r['materiel_nom']) ?></td>
                            <td class="text-danger fw-bold"><?= dateFr($r['date_retour_prevue']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<!-- ===== Deux listes côte à côte : derniers prêts / dernières réservations ===== -->
<div class="row g-3">
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-clock-history"></i> Derniers prêts</span>
                <a href="<?= url('pret', 'form') ?>" class="btn btn-sm btn-mediatheque">
                    <i class="bi bi-plus-lg"></i> Prêt
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0 align-middle">
                    <thead><tr><th>Adhérent</th><th>Produit</th><th>Statut</th></tr></thead>
                    <tbody>
                        <?php if (empty($prets)): ?>
                            <tr><td colspan="3" class="text-center text-muted py-3">Aucun prêt.</td></tr>
                        <?php else: foreach ($prets as $p): ?>
                            <tr>
                                <td><?= e($p['adherent_nom']) ?></td>
                                <td><?= e($p['livre_titre'] ?? $p['materiel_nom']) ?></td>
                                <td>
                                    <?php // Une date de retour effective = le prêt est terminé ?>
                                    <?php if ($p['date_retour_effective']): ?>
                                        <span class="badge bg-success">Rendu</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">En cours</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-calendar-week"></i> Dernières réservations</span>
                <a href="<?= url('reservation', 'form') ?>" class="btn btn-sm btn-mediatheque">
                    <i class="bi bi-plus-lg"></i> Réservation
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0 align-middle">
                    <thead><tr><th>Adhérent</th><th>Salle</th><th>Date</th></tr></thead>
                    <tbody>
                        <?php if (empty($reservations)): ?>
                            <tr><td colspan="3" class="text-center text-muted py-3">Aucune réservation.</td></tr>
                        <?php else: foreach ($reservations as $r): ?>
                            <tr>
                                <td><?= e($r['adherent_nom']) ?></td>
                                <td><?= e($r['salle_nom']) ?></td>
                                <td><?= dateFr($r['date_reservation']) ?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
