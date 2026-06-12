<?php
// Liste des salles, présentées en cartes (et non en tableau,
// pour mettre en avant la capacité et les équipements).
?>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h1 class="h3 page-title mb-0"><i class="bi bi-door-open"></i> Gestion des salles de coworking</h1>
    <a href="<?= url('salle', 'form') ?>" class="btn btn-mediatheque">
        <i class="bi bi-plus-lg"></i> Nouvelle salle
    </a>
</div>

<div class="row g-3">
    <?php if (empty($salles)): ?>
        <p class="text-muted">Aucune salle enregistrée.</p>
    <?php else: foreach ($salles as $s): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <h5 class="card-title mb-1"><i class="bi bi-door-closed"></i> <?= e($s['nom']) ?></h5>
                        <?php if ($s['disponible']): ?>
                            <span class="badge text-bg-success">Disponible</span>
                        <?php else: ?>
                            <span class="badge text-bg-secondary">Indisponible</span>
                        <?php endif; ?>
                    </div>
                    <p class="text-muted mb-2"><i class="bi bi-people"></i> Capacité : <?= (int) $s['capacite'] ?> personnes</p>
                    <p class="small mb-3"><?= e($s['equipement']) ?: '<span class="text-muted">Aucun équipement renseigné</span>' ?></p>
                    <div class="d-flex gap-2 align-items-center">
                        <a href="<?= url('reservation', 'form') ?>" class="btn btn-sm btn-mediatheque">
                            <i class="bi bi-calendar-plus"></i> Réserver
                        </a>
                        <a href="<?= url('salle', 'form', ['id' => $s['id']]) ?>"
                           class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <?= postButton('salle', 'delete', (int) $s['id'], '<i class="bi bi-trash"></i>', [
                            'class'   => 'btn btn-sm btn-outline-danger',
                            'confirm' => 'Supprimer cette salle ?',
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; endif; ?>
</div>
