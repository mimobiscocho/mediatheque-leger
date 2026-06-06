<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h1 class="h3 page-title mb-0"><i class="bi bi-book"></i> Gestion des livres</h1>
    <a href="<?= url('livre', 'form') ?>" class="btn btn-mediatheque">
        <i class="bi bi-plus-lg"></i> Nouveau livre
    </a>
</div>

<!-- Filtres multicritères (soumis en GET au contrôleur Livre) -->
<form method="get" action="<?= BASE_URL ?>" class="card card-body shadow-sm mb-3">
    <input type="hidden" name="ctrl" value="livre">
    <input type="hidden" name="action" value="index">
    <div class="row g-2 align-items-end">
        <div class="col-md-5">
            <label class="form-label mb-1 small fw-semibold">Recherche</label>
            <input type="search" name="q" class="form-control" value="<?= e($filtres['q']) ?>"
                   placeholder="🔎 Titre, auteur ou ISBN…">
        </div>
        <div class="col-md-3">
            <label class="form-label mb-1 small fw-semibold">Genre</label>
            <select name="genre" class="form-select">
                <option value="">Tous les genres</option>
                <?php foreach ($genres as $g): ?>
                    <option value="<?= e($g) ?>" <?= $filtres['genre'] === $g ? 'selected' : '' ?>><?= e($g) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label mb-1 small fw-semibold">Disponibilité</label>
            <select name="dispo" class="form-select">
                <option value="">Toutes</option>
                <option value="1" <?= $filtres['dispo'] === '1' ? 'selected' : '' ?>>Disponibles</option>
                <option value="0" <?= $filtres['dispo'] === '0' ? 'selected' : '' ?>>Indisponibles</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-mediatheque w-100"><i class="bi bi-funnel"></i> Filtrer</button>
            <a href="<?= url('livre') ?>" class="btn btn-outline-secondary" title="Réinitialiser les filtres"><i class="bi bi-x-lg"></i></a>
        </div>
    </div>
</form>

<p class="text-muted small"><?= count($livres) ?> livre(s) trouvé(s).</p>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="tbl-livres">
            <thead>
                <tr>
                    <th>Titre</th><th>Auteur</th><th>Genre</th><th>Année</th>
                    <th class="text-center">Disponibilité</th><th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($livres)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">Aucun livre enregistré.</td></tr>
                <?php else: foreach ($livres as $l): ?>
                    <tr>
                        <td class="fw-semibold"><?= e($l['titre']) ?></td>
                        <td><?= e($l['auteur']) ?></td>
                        <td><?= e($l['genre']) ?: '—' ?></td>
                        <td><?= e($l['annee_publication']) ?: '—' ?></td>
                        <td class="text-center">
                            <?php
                            $dispo = (int) $l['quantite_disponible'];
                            $cls = $dispo > 0 ? 'text-bg-success' : 'text-bg-danger';
                            ?>
                            <span class="badge <?= $cls ?>"><?= $dispo ?> / <?= (int) $l['quantite_totale'] ?></span>
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="<?= url('livre', 'form', ['id' => $l['id']]) ?>"
                               class="btn btn-sm btn-outline-primary" title="Modifier"><i class="bi bi-pencil"></i></a>
                            <?= postButton('livre', 'delete', (int) $l['id'], '<i class="bi bi-trash"></i>', [
                                'class'   => 'btn btn-sm btn-outline-danger',
                                'title'   => 'Supprimer',
                                'confirm' => 'Supprimer ce livre ?',
                            ]) ?>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
