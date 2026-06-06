<?php
$action = $materiel ? url('materiel', 'save', ['id' => $materiel['id']]) : url('materiel', 'save');
$etats  = ['neuf' => 'Neuf', 'bon' => 'Bon', 'use' => 'Usé', 'hors_service' => 'Hors service'];
?>
<h1 class="h3 mb-4 page-title"><i class="bi bi-tools"></i> <?= e($titre) ?></h1>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="post" action="<?= $action ?>" class="row g-3">
            <?= csrf_field() ?>
            <div class="col-md-8">
                <label class="form-label">Nom <span class="text-danger">*</span></label>
                <input type="text" name="nom" class="form-control" required
                       value="<?= e($materiel['nom'] ?? '') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Catégorie</label>
                <input type="text" name="categorie" class="form-control"
                       value="<?= e($materiel['categorie'] ?? '') ?>" placeholder="Informatique, Audiovisuel…">
            </div>
            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="2"><?= e($materiel['description'] ?? '') ?></textarea>
            </div>
            <div class="col-md-4">
                <label class="form-label">État</label>
                <select name="etat" class="form-select">
                    <?php foreach ($etats as $val => $lbl): ?>
                        <option value="<?= $val ?>" <?= (($materiel['etat'] ?? 'bon') === $val) ? 'selected' : '' ?>>
                            <?= $lbl ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-8 d-flex align-items-end">
                <div class="form-check form-switch">
                    <input type="checkbox" name="disponible" value="1" class="form-check-input" id="dispo"
                        <?= (!$materiel || $materiel['disponible']) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="dispo">Disponible à l'emprunt</label>
                </div>
            </div>
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-mediatheque"><i class="bi bi-check-lg"></i> Enregistrer</button>
                <a href="<?= url('materiel') ?>" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
