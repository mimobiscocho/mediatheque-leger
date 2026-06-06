<?php $action = $livre ? url('livre', 'save', ['id' => $livre['id']]) : url('livre', 'save'); ?>

<h1 class="h3 mb-4 page-title"><i class="bi bi-book"></i> <?= e($titre) ?></h1>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="post" action="<?= $action ?>" class="row g-3">
            <?= csrf_field() ?>
            <div class="col-md-8">
                <label class="form-label">Titre <span class="text-danger">*</span></label>
                <input type="text" name="titre" class="form-control" required
                       value="<?= e($livre['titre'] ?? '') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Auteur <span class="text-danger">*</span></label>
                <input type="text" name="auteur" class="form-control" required
                       value="<?= e($livre['auteur'] ?? '') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">ISBN</label>
                <input type="text" name="isbn" class="form-control"
                       value="<?= e($livre['isbn'] ?? '') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Éditeur</label>
                <input type="text" name="editeur" class="form-control"
                       value="<?= e($livre['editeur'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Année</label>
                <input type="number" name="annee_publication" class="form-control" min="0" max="2100"
                       value="<?= e($livre['annee_publication'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Genre</label>
                <input type="text" name="genre" class="form-control"
                       value="<?= e($livre['genre'] ?? '') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Quantité totale</label>
                <input type="number" name="quantite_totale" class="form-control" min="0"
                       value="<?= e($livre['quantite_totale'] ?? '1') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Exemplaires disponibles</label>
                <input type="number" name="quantite_disponible" class="form-control" min="0"
                       value="<?= e($livre['quantite_disponible'] ?? '1') ?>">
                <div class="form-text">Ne peut dépasser la quantité totale.</div>
            </div>
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-mediatheque"><i class="bi bi-check-lg"></i> Enregistrer</button>
                <a href="<?= url('livre') ?>" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
