<?php
// Tableau de bord de l'espace adhérent : récap. + liste des réservations.
$statutCls = ['CONFIRMEE' => 'text-bg-success', 'ANNULEE' => 'text-bg-secondary', 'TERMINEE' => 'text-bg-dark'];
$statutLbl = ['CONFIRMEE' => 'Confirmée',       'ANNULEE' => 'Annulée',          'TERMINEE' => 'Terminée'];

$today  = date('Y-m-d');
$aVenir = array_filter($reservations, fn($r) => $r['statut'] === 'CONFIRMEE' && $r['date_reservation'] >= $today);

require __DIR__ . '/_header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 page-title mb-0">
            <i class="bi bi-house-door"></i>
            Bonjour, <?= e($_SESSION['client']['prenom']) ?> !
        </h1>
        <p class="text-muted small mb-0 mt-1">Vos réservations de salles en un coup d'œil.</p>
    </div>
    <a href="<?= url('client', 'reserver') ?>" class="btn btn-mediatheque">
        <i class="bi bi-plus-lg"></i> Nouvelle réservation
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card card-stat shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">À venir</div>
                <div class="h3 mb-0">
                    <i class="bi bi-calendar-event"></i> <?= count($aVenir) ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-stat shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Total réservations</div>
                <div class="h3 mb-0">
                    <i class="bi bi-calendar-check"></i> <?= count($reservations) ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-stat shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Email du compte</div>
                <div class="fw-semibold text-truncate">
                    <i class="bi bi-envelope"></i> <?= e($_SESSION['client']['email']) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white">
        <strong>Mes réservations</strong>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Salle</th>
                    <th>Date</th>
                    <th>Créneau</th>
                    <th>Statut</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($reservations)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            Vous n'avez pas encore réservé de salle.
                            <a href="<?= url('client', 'reserver') ?>">Réserver maintenant</a>.
                        </td>
                    </tr>
                <?php else: foreach ($reservations as $r): ?>
                    <tr>
                        <td class="fw-semibold"><?= e($r['salle_nom']) ?></td>
                        <td><?= dateFr($r['date_reservation']) ?></td>
                        <td>
                            <?= substr($r['heure_debut'], 0, 5) ?> –
                            <?= substr($r['heure_fin'],   0, 5) ?>
                        </td>
                        <td>
                            <span class="badge <?= $statutCls[$r['statut']] ?>">
                                <?= $statutLbl[$r['statut']] ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <?php if ($r['statut'] === 'CONFIRMEE' && $r['date_reservation'] >= $today): ?>
                                <?= postButton('client', 'annuler', (int) $r['id'],
                                    '<i class="bi bi-x-circle"></i> Annuler', [
                                        'class'   => 'btn btn-sm btn-outline-warning',
                                        'confirm' => 'Annuler cette réservation ?',
                                ]) ?>
                            <?php else: ?>
                                <span class="text-muted small">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
