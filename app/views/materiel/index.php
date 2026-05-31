<?php
$etatLabels = ['neuf' => 'Neuf', 'bon' => 'Bon', 'use' => 'Usé', 'hors_service' => 'Hors service'];
$etatCls    = ['neuf' => 'text-bg-success', 'bon' => 'text-bg-primary', 'use' => 'text-bg-warning', 'hors_service' => 'text-bg-secondary'];
?>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h1 class="h3 page-title mb-0"><i class="bi bi-tools"></i> Gestion des matériels</h1>
    <a href="<?= url('materiel', 'form') ?>" class="btn btn-mediatheque">
        <i class="bi bi-plus-lg"></i> Nouveau matériel
    </a>
</div>

<input type="search" class="form-control mb-3" data-filter="#tbl-materiels"
       placeholder="🔎 Rechercher un matériel…">

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="tbl-materiels">
            <thead>
                <tr>
                    <th>Nom</th><th>Catégorie</th><th>Description</th>
                    <th>État</th><th class="text-center">Disponible</th><th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($materiels)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">Aucun matériel enregistré.</td></tr>
                <?php else: foreach ($materiels as $m): ?>
                    <tr>
                        <td class="fw-semibold"><?= e($m['nom']) ?></td>
                        <td><?= e($m['categorie']) ?: '—' ?></td>
                        <td class="text-muted small"><?= e($m['description']) ?: '—' ?></td>
                        <td><span class="badge <?= $etatCls[$m['etat']] ?>"><?= $etatLabels[$m['etat']] ?></span></td>
                        <td class="text-center">
                            <?php if ($m['disponible']): ?>
                                <i class="bi bi-check-circle-fill text-success fs-5"></i>
                            <?php else: ?>
                                <i class="bi bi-x-circle-fill text-danger fs-5"></i>
                            <?php endif; ?>
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="<?= url('materiel', 'form', ['id' => $m['id']]) ?>"
                               class="btn btn-sm btn-outline-primary" title="Modifier"><i class="bi bi-pencil"></i></a>
                            <a href="<?= url('materiel', 'delete', ['id' => $m['id']]) ?>"
                               class="btn btn-sm btn-outline-danger" title="Supprimer"
                               data-confirm="Supprimer ce matériel ?"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
