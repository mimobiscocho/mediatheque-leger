<h1 class="h3 mb-4 page-title"><i class="bi bi-speedometer2"></i> Tableau de bord</h1>

<!-- Cartes de statistiques -->
<div class="row g-3 mb-4">
    <?php
    $cards = [
        ['Adhérents', $stats['adherents'], 'bi-people',     'adherent'],
        ['Livres',    $stats['livres'],    'bi-book',       'livre'],
        ['Matériels', $stats['materiels'], 'bi-tools',      'materiel'],
        ['Salles',    $stats['salles'],    'bi-door-open',  'salle'],
    ];
    foreach ($cards as [$lbl, $val, $icon, $ctrl]): ?>
        <div class="col-6 col-lg-3">
            <a href="<?= url($ctrl) ?>" class="text-decoration-none text-reset">
                <div class="card card-stat shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <i class="bi <?= $icon ?> fs-1 me-3" style="color:var(--mediatheque)"></i>
                        <div>
                            <div class="fs-3 fw-bold"><?= (int) $val ?></div>
                            <div class="text-muted small"><?= $lbl ?></div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>

<!-- Indicateurs d'activité -->
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card text-bg-primary shadow-sm h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="fs-1 fw-bold"><?= (int) $pretsEnCours ?></div>
                    <div>Prêt(s) en cours</div>
                </div>
                <a href="<?= url('pret') ?>" class="btn btn-light btn-sm">
                    <i class="bi bi-arrow-left-right"></i> Gérer
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card text-bg-info shadow-sm h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="fs-1 fw-bold"><?= (int) $resaActives ?></div>
                    <div>Réservation(s) à venir</div>
                </div>
                <a href="<?= url('reservation') ?>" class="btn btn-light btn-sm">
                    <i class="bi bi-calendar-check"></i> Gérer
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Prêts en retard -->
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
                            <td><?= e($r['livre_titre'] ?? $r['materiel_nom']) ?></td>
                            <td class="text-danger fw-semibold"><?= dateFr($r['date_retour_prevue']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<div class="row g-3">
    <!-- Derniers prêts -->
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
                                    <?php if ($p['date_retour_effective']): ?>
                                        <span class="badge text-bg-success">Rendu</span>
                                    <?php else: ?>
                                        <span class="badge text-bg-warning">En cours</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Dernières réservations -->
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
