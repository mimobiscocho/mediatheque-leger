<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h1 class="h3 page-title mb-0"><i class="bi bi-book"></i> Gestion des livres</h1>
    <a href="<?= url('livre', 'form') ?>" class="btn btn-mediatheque">
        <i class="bi bi-plus-lg"></i> Nouveau livre
    </a>
</div>

<input type="search" class="form-control mb-3" data-filter="#tbl-livres"
       placeholder="🔎 Rechercher un livre (titre, auteur, genre…)">

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
                            <a href="<?= url('livre', 'delete', ['id' => $l['id']]) ?>"
                               class="btn btn-sm btn-outline-danger" title="Supprimer"
                               data-confirm="Supprimer ce livre ?"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
