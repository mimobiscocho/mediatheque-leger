<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h1 class="h3 page-title mb-0"><i class="bi bi-arrow-left-right"></i> Système de prêts</h1>
    <a href="<?= url('pret', 'form') ?>" class="btn btn-mediatheque">
        <i class="bi bi-plus-lg"></i> Nouveau prêt
    </a>
</div>

<input type="search" class="form-control mb-3" data-filter="#tbl-prets"
       placeholder="🔎 Rechercher (adhérent, produit…)">

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="tbl-prets">
            <thead>
                <tr>
                    <th>Adhérent</th><th>Produit</th><th>Type</th>
                    <th>Emprunt</th><th>Retour prévu</th><th>Statut</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($prets)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">Aucun prêt enregistré.</td></tr>
                <?php else: foreach ($prets as $p):
                    $rendu     = !empty($p['date_retour_effective']);
                    $enRetard  = !$rendu && $p['date_retour_prevue'] < date('Y-m-d');
                ?>
                    <tr>
                        <td class="fw-semibold"><?= e($p['adherent_nom']) ?></td>
                        <td><?= e($p['livre_titre'] ?? $p['materiel_nom']) ?></td>
                        <td>
                            <?php if ($p['livre_id']): ?>
                                <span class="badge text-bg-info"><i class="bi bi-book"></i> Livre</span>
                            <?php else: ?>
                                <span class="badge text-bg-dark"><i class="bi bi-tools"></i> Matériel</span>
                            <?php endif; ?>
                        </td>
                        <td><?= dateFr($p['date_pret']) ?></td>
                        <td><?= dateFr($p['date_retour_prevue']) ?></td>
                        <td>
                            <?php if ($rendu): ?>
                                <span class="badge text-bg-success">Rendu le <?= dateFr($p['date_retour_effective']) ?></span>
                            <?php elseif ($enRetard): ?>
                                <span class="badge text-bg-danger">En retard</span>
                            <?php else: ?>
                                <span class="badge text-bg-warning">En cours</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end text-nowrap">
                            <?php if (!$rendu): ?>
                                <a href="<?= url('pret', 'retour', ['id' => $p['id']]) ?>"
                                   class="btn btn-sm btn-success" title="Enregistrer le retour"
                                   data-confirm="Confirmer le retour de ce produit ?">
                                    <i class="bi bi-box-arrow-in-down"></i> Retour
                                </a>
                            <?php endif; ?>
                            <a href="<?= url('pret', 'delete', ['id' => $p['id']]) ?>"
                               class="btn btn-sm btn-outline-danger" title="Supprimer"
                               data-confirm="Supprimer ce prêt ?"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
