<?php
// Formulaire de réservation côté client. L'adhérent_id est forcé côté
// serveur à celui de la session : pas de sélection ici.
$today = date('Y-m-d');
require __DIR__ . '/_header.php';
?>

<h1 class="h3 mb-4 page-title">
    <i class="bi bi-calendar-plus"></i> <?= e($titre) ?>
</h1>

<div class="card shadow-sm">
    <div class="card-body">
        <?php if (empty($salles)): ?>
            <div class="alert alert-warning mb-0">
                Aucune salle n'est disponible pour le moment.
            </div>
        <?php else: ?>
        <form method="post" action="<?= url('client', 'saveReservation') ?>" class="row g-3">
            <?= csrf_field() ?>

            <div class="col-md-12">
                <label class="form-label">Salle <span class="text-danger">*</span></label>
                <select name="salle_id" class="form-select" required>
                    <option value="">— Choisir une salle —</option>
                    <?php foreach ($salles as $s): ?>
                        <option value="<?= (int) $s['id'] ?>" <?= $s['disponible'] ? '' : 'disabled' ?>>
                            <?= e($s['nom']) ?>
                            (<?= (int) $s['capacite'] ?> pers.)
                            <?php if (!empty($s['equipement'])): ?>
                                — <?= e($s['equipement']) ?>
                            <?php endif; ?>
                            <?= $s['disponible'] ? '' : ' — indisponible' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Date</label>
                <input type="date" name="date_reservation" class="form-control"
                       value="<?= $today ?>" min="<?= $today ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Heure de début</label>
                <input type="time" name="heure_debut" class="form-control" value="09:00" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Heure de fin</label>
                <input type="time" name="heure_fin" class="form-control" value="10:00" required>
            </div>

            <div class="col-12">
                <div class="alert alert-info py-2 small mb-0">
                    <i class="bi bi-info-circle"></i>
                    La salle est vérifiée automatiquement : un créneau déjà pris
                    ou une salle indisponible refusera la réservation.
                </div>
            </div>

            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-mediatheque">
                    <i class="bi bi-check-lg"></i> Confirmer la réservation
                </button>
                <a href="<?= url('client', 'dashboard') ?>" class="btn btn-outline-secondary">
                    Retour
                </a>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
