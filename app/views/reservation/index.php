<?php
$statutCls = ['confirmee' => 'text-bg-success', 'annulee' => 'text-bg-secondary', 'terminee' => 'text-bg-dark'];
$statutLbl = ['confirmee' => 'Confirmée', 'annulee' => 'Annulée', 'terminee' => 'Terminée'];
?>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h1 class="h3 page-title mb-0"><i class="bi bi-calendar-check"></i> Réservation d'espaces</h1>
    <a href="<?= url('reservation', 'form') ?>" class="btn btn-mediatheque">
        <i class="bi bi-plus-lg"></i> Nouvelle réservation
    </a>
</div>

<input type="search" class="form-control mb-3" data-filter="#tbl-resa"
       placeholder="🔎 Rechercher (adhérent, salle…)">

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="tbl-resa">
            <thead>
                <tr>
                    <th>Adhérent</th><th>Salle</th><th>Date</th>
                    <th>Créneau</th><th>Statut</th><th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($reservations)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">Aucune réservation enregistrée.</td></tr>
                <?php else: foreach ($reservations as $r): ?>
                    <tr>
                        <td class="fw-semibold"><?= e($r['adherent_nom']) ?></td>
                        <td><?= e($r['salle_nom']) ?></td>
                        <td><?= dateFr($r['date_reservation']) ?></td>
                        <td><?= substr($r['heure_debut'], 0, 5) ?> – <?= substr($r['heure_fin'], 0, 5) ?></td>
                        <td><span class="badge <?= $statutCls[$r['statut']] ?>"><?= $statutLbl[$r['statut']] ?></span></td>
                        <td class="text-end text-nowrap">
                            <?php if ($r['statut'] === 'confirmee'): ?>
                                <a href="<?= url('reservation', 'annuler', ['id' => $r['id']]) ?>"
                                   class="btn btn-sm btn-outline-warning" title="Annuler"
                                   data-confirm="Annuler cette réservation ?"><i class="bi bi-x-circle"></i> Annuler</a>
                            <?php endif; ?>
                            <a href="<?= url('reservation', 'delete', ['id' => $r['id']]) ?>"
                               class="btn btn-sm btn-outline-danger" title="Supprimer"
                               data-confirm="Supprimer cette réservation ?"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
