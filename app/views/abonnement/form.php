<h1 class="h3 mb-4 page-title"><i class="bi bi-credit-card"></i> <?= e($titre) ?></h1>

<div class="card shadow-sm">
    <div class="card-body">
        <?php // L'action contient l'id en cas de modification, rien en création ?>
        <form method="post" action="<?= url('abonnement', 'save', $abonnement ? ['id' => $abonnement['id']] : []) ?>">
            <?= csrf_field() // jeton anti-CSRF obligatoire sur tout POST ?>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Libellé <span class="text-danger">*</span></label>
                    <input type="text" name="libelle" class="form-control" required
                           value="<?= e($abonnement['libelle'] ?? '') ?>"
                           placeholder="Ex : Standard, Étudiant, Premium">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tarif annuel (&euro;)</label>
                    <input type="number" name="tarif" class="form-control" step="0.01" min="0"
                           value="<?= e($abonnement['tarif'] ?? '0') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Durée (mois)</label>
                    <input type="number" name="duree_mois" class="form-control" min="1"
                           value="<?= e($abonnement['duree_mois'] ?? '12') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Quota d'emprunts simultanés</label>
                    <input type="number" name="quota_emprunts" class="form-control" min="1"
                           value="<?= e($abonnement['quota_emprunts'] ?? '5') ?>">
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-mediatheque">
                    <i class="bi bi-check-lg"></i> Enregistrer
                </button>
                <a href="<?= url('abonnement') ?>" class="btn btn-secondary ms-2">Annuler</a>
            </div>
        </form>
    </div>
</div>
