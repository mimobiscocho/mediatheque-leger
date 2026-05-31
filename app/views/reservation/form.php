<?php $today = date('Y-m-d'); ?>

<h1 class="h3 mb-4 page-title"><i class="bi bi-calendar-check"></i> <?= e($titre) ?></h1>

<div class="card shadow-sm">
    <div class="card-body">
        <?php if (empty($salles)): ?>
            <div class="alert alert-warning mb-0">Aucune salle n'est enregistrée. Créez d'abord une salle.</div>
        <?php else: ?>
        <form method="post" action="<?= url('reservation', 'save') ?>" class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Adhérent <span class="text-danger">*</span></label>
                <select name="adherent_id" class="form-select" required>
                    <option value="">— Choisir un adhérent —</option>
                    <?php foreach ($adherents as $a): ?>
                        <option value="<?= $a['id'] ?>"><?= e($a['nom']) ?> <?= e($a['prenom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Salle <span class="text-danger">*</span></label>
                <select name="salle_id" class="form-select" required>
                    <option value="">— Choisir une salle —</option>
                    <?php foreach ($salles as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= $s['disponible'] ? '' : 'disabled' ?>>
                            <?= e($s['nom']) ?> (<?= (int) $s['capacite'] ?> pers.)<?= $s['disponible'] ? '' : ' — indisponible' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Date</label>
                <input type="date" name="date_reservation" class="form-control" value="<?= $today ?>" min="<?= $today ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Heure de début</label>
                <input type="time" name="heure_debut" class="form-control" value="09:00">
            </div>
            <div class="col-md-4">
                <label class="form-label">Heure de fin</label>
                <input type="time" name="heure_fin" class="form-control" value="10:00">
            </div>
            <div class="col-12">
                <div class="alert alert-info py-2 small mb-0">
                    <i class="bi bi-info-circle"></i>
                    Les conflits de créneau sur une même salle sont bloqués automatiquement
                    par la base de données (trigger).
                </div>
            </div>
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-mediatheque"><i class="bi bi-check-lg"></i> Confirmer la réservation</button>
                <a href="<?= url('reservation') ?>" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>
