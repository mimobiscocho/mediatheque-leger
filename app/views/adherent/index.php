<?php
// Liste des adhérents. $adherents est fourni par AdherentController::index
// (avec le libellé de l'abonnement déjà joint en SQL).
?>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h1 class="h3 page-title mb-0"><i class="bi bi-people"></i> Gestion des adhérents</h1>
    <a href="<?= url('adherent', 'form') ?>" class="btn btn-mediatheque">
        <i class="bi bi-plus-lg"></i> Nouvel adhérent
    </a>
</div>

<?php // Champ de recherche instantanée : filtré en JavaScript (voir app.js),
      // l'attribut data-filter pointe vers le tableau à filtrer. ?>
<input type="search" class="form-control mb-3" data-filter="#tbl-adherents"
       placeholder="Rechercher un adhérent (nom, email...)">

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="tbl-adherents">
            <thead>
                <tr>
                    <th>Nom</th><th>Email</th><th>Téléphone</th>
                    <th>Abonnement</th><th>Inscription</th><th>Statut</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($adherents)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">Aucun adhérent enregistré.</td></tr>
                <?php else: foreach ($adherents as $a): ?>
                    <tr>
                        <td class="fw-semibold"><?= e($a['nom']) ?> <?= e($a['prenom']) ?></td>
                        <td><?= e($a['email']) ?></td>
                        <td><?= e($a['telephone']) ?: '—' ?></td>
                        <td><?= e($a['abonnement_libelle'] ?? '') ?: '—' ?></td>
                        <td><?= dateFr($a['date_inscription']) ?></td>
                        <td>
                            <?php if ($a['actif']): ?>
                                <span class="badge text-bg-success">Actif</span>
                            <?php else: ?>
                                <span class="badge text-bg-secondary">Inactif</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end text-nowrap">
                            <?php // Modifier = simple lien ; Supprimer = formulaire POST + CSRF ?>
                            <a href="<?= url('adherent', 'form', ['id' => $a['id']]) ?>"
                               class="btn btn-sm btn-outline-primary" title="Modifier"><i class="bi bi-pencil"></i></a>
                            <?= postButton('adherent', 'delete', (int) $a['id'], '<i class="bi bi-trash"></i>', [
                                'class'   => 'btn btn-sm btn-outline-danger',
                                'title'   => 'Supprimer',
                                'confirm' => 'Supprimer définitivement cet adhérent ?',
                            ]) ?>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
