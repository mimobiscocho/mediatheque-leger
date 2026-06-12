<?php
// Liste des abonnements. Tous les agents la consultent, mais les boutons
// d'ajout / modification / suppression ne s'affichent que pour un admin
// (et le contrôleur re-vérifie le rôle côté serveur de toute façon).
?>
<h1 class="h3 mb-4 page-title"><i class="bi bi-credit-card"></i> <?= e($titre) ?></h1>

<?php if (isAdmin()): ?>
    <a href="<?= url('abonnement', 'form') ?>" class="btn btn-mediatheque mb-3">
        <i class="bi bi-plus-lg"></i> Nouvel abonnement
    </a>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>Libellé</th>
                    <th>Tarif</th>
                    <th>Durée</th>
                    <th>Quota emprunts</th>
                    <?php if (isAdmin()): ?><th class="text-end">Actions</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($abonnements)): ?>
                    <tr><td colspan="5" class="text-center text-muted py-3">Aucun abonnement.</td></tr>
                <?php else: foreach ($abonnements as $a): ?>
                    <tr>
                        <td class="fw-semibold"><?= e($a['libelle']) ?></td>
                        <td><?= number_format($a['tarif'], 2, ',', ' ') ?> &euro;</td>
                        <td><?= (int) $a['duree_mois'] ?> mois</td>
                        <td><?= (int) $a['quota_emprunts'] ?></td>
                        <?php if (isAdmin()): ?>
                            <td class="text-end">
                                <a href="<?= url('abonnement', 'form', ['id' => $a['id']]) ?>"
                                   class="btn btn-sm btn-outline-primary" title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php // Suppression en POST + jeton CSRF (jamais via simple lien GET) ?>
                                <?= postButton('abonnement', 'delete', (int) $a['id'], '<i class="bi bi-trash"></i>', [
                                    'class'   => 'btn btn-sm btn-outline-danger',
                                    'title'   => 'Supprimer',
                                    'confirm' => 'Supprimer cet abonnement ?',
                                ]) ?>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
