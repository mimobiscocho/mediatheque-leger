<?php $today = date('Y-m-d'); $plus14 = date('Y-m-d', strtotime('+14 days')); ?>

<h1 class="h3 mb-4 page-title"><i class="bi bi-arrow-left-right"></i> <?= e($titre) ?></h1>

<div class="card shadow-sm">
    <div class="card-body">
        <?php if (empty($livres) && empty($materiels)): ?>
            <div class="alert alert-warning mb-0">
                Aucun produit n'est disponible à l'emprunt actuellement.
            </div>
        <?php else: ?>
        <form method="post" action="<?= url('pret', 'save') ?>" class="row g-3">
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
                <label class="form-label">Produit à emprunter <span class="text-danger">*</span></label>
                <select name="produit" class="form-select" required>
                    <option value="">— Choisir un produit —</option>
                    <?php if (!empty($livres)): ?>
                        <optgroup label="📚 Livres">
                            <?php foreach ($livres as $l): ?>
                                <option value="livre:<?= $l['id'] ?>">
                                    <?= e($l['titre']) ?> — <?= e($l['auteur']) ?>
                                    (<?= (int) $l['quantite_disponible'] ?> dispo)
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endif; ?>
                    <?php if (!empty($materiels)): ?>
                        <optgroup label="🔧 Matériels">
                            <?php foreach ($materiels as $m): ?>
                                <option value="materiel:<?= $m['id'] ?>"><?= e($m['nom']) ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Date du prêt</label>
                <input type="date" name="date_pret" class="form-control" value="<?= $today ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Date de retour prévue</label>
                <input type="date" name="date_retour_prevue" class="form-control" value="<?= $plus14 ?>">
            </div>
            <div class="col-12">
                <div class="alert alert-info py-2 small mb-0">
                    <i class="bi bi-info-circle"></i>
                    La disponibilité du produit est vérifiée automatiquement par la base de données
                    (trigger) au moment de l'enregistrement.
                </div>
            </div>
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-mediatheque"><i class="bi bi-check-lg"></i> Enregistrer le prêt</button>
                <a href="<?= url('pret') ?>" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>
