<?php
// Formulaire d'adhérent : sert à la fois pour la CRÉATION ($adherent vaut
// null) et la MODIFICATION ($adherent contient la ligne lue en base).
// En modification, on ajoute l'id dans l'URL de soumission.
$action = $adherent ? url('adherent', 'save', ['id' => $adherent['id']]) : url('adherent', 'save');
?>

<h1 class="h3 mb-4 page-title"><i class="bi bi-person-plus"></i> <?= e($titre) ?></h1>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="post" action="<?= $action ?>" class="row g-3">
            <?= csrf_field() ?>
            <div class="col-md-6">
                <label class="form-label">Nom <span class="text-danger">*</span></label>
                <input type="text" name="nom" class="form-control" required
                       value="<?= e($adherent['nom'] ?? '') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Prénom <span class="text-danger">*</span></label>
                <input type="text" name="prenom" class="form-control" required
                       value="<?= e($adherent['prenom'] ?? '') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control" required
                       value="<?= e($adherent['email'] ?? '') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Téléphone</label>
                <input type="tel" name="telephone" class="form-control"
                       value="<?= e($adherent['telephone'] ?? '') ?>">
            </div>
            <div class="col-12">
                <label class="form-label">Adresse</label>
                <input type="text" name="adresse" class="form-control"
                       value="<?= e($adherent['adresse'] ?? '') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Abonnement</label>
                <select name="abonnement_id" class="form-select">
                    <option value="">— Aucun —</option>
                    <?php foreach ($abonnements as $ab): ?>
                        <option value="<?= $ab['id'] ?>"
                            <?= (isset($adherent['abonnement_id']) && $adherent['abonnement_id'] == $ab['id']) ? 'selected' : '' ?>>
                            <?= e($ab['libelle']) ?> (<?= number_format($ab['tarif'], 2, ',', ' ') ?> €)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Date d'inscription</label>
                <input type="date" name="date_inscription" class="form-control"
                       value="<?= e($adherent['date_inscription'] ?? date('Y-m-d')) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Fin d'abonnement</label>
                <input type="date" name="date_fin_abonnement" class="form-control"
                       value="<?= e($adherent['date_fin_abonnement'] ?? '') ?>">
            </div>
            <div class="col-12">
                <div class="form-check form-switch">
                    <input type="checkbox" name="actif" value="1" class="form-check-input" id="actif"
                        <?= (!$adherent || $adherent['actif']) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="actif">Adhérent actif</label>
                </div>
            </div>
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-mediatheque"><i class="bi bi-check-lg"></i> Enregistrer</button>
                <a href="<?= url('adherent') ?>" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
