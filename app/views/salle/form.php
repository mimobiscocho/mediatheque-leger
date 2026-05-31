<?php $action = $salle ? url('salle', 'save', ['id' => $salle['id']]) : url('salle', 'save'); ?>

<h1 class="h3 mb-4 page-title"><i class="bi bi-door-open"></i> <?= e($titre) ?></h1>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="post" action="<?= $action ?>" class="row g-3">
            <div class="col-md-8">
                <label class="form-label">Nom de la salle <span class="text-danger">*</span></label>
                <input type="text" name="nom" class="form-control" required
                       value="<?= e($salle['nom'] ?? '') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Capacité (personnes)</label>
                <input type="number" name="capacite" class="form-control" min="1"
                       value="<?= e($salle['capacite'] ?? '1') ?>">
            </div>
            <div class="col-12">
                <label class="form-label">Équipements</label>
                <input type="text" name="equipements" class="form-control"
                       value="<?= e($salle['equipements'] ?? '') ?>"
                       placeholder="Wi-Fi, vidéoprojecteur, tableau blanc…">
            </div>
            <div class="col-12">
                <div class="form-check form-switch">
                    <input type="checkbox" name="disponible" value="1" class="form-check-input" id="dispo"
                        <?= (!$salle || $salle['disponible']) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="dispo">Salle disponible à la réservation</label>
                </div>
            </div>
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-mediatheque"><i class="bi bi-check-lg"></i> Enregistrer</button>
                <a href="<?= url('salle') ?>" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
