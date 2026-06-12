<?php
// Page "Mon profil" : informations du compte connecté (lecture seule)
// et formulaire de changement de mot de passe.
?>
<h1 class="h3 mb-4 page-title"><i class="bi bi-person-circle"></i> <?= e($titre) ?></h1>

<div class="row g-4">
    <!-- Informations du profil -->
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header"><i class="bi bi-person-badge"></i> Mes informations</div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr><th class="text-muted" style="width:40%">Nom</th><td><?= e($agent['prenom'] . ' ' . $agent['nom']) ?></td></tr>
                    <tr><th class="text-muted">Email</th><td><?= e($agent['email']) ?></td></tr>
                    <tr>
                        <th class="text-muted">Rôle</th>
                        <td>
                            <span class="badge text-bg-<?= $agent['role'] === 'admin' ? 'danger' : 'secondary' ?>">
                                <?= e(ucfirst($agent['role'])) ?>
                            </span>
                        </td>
                    </tr>
                    <tr><th class="text-muted">Compte créé le</th><td><?= dateFr($agent['date_creation']) ?></td></tr>
                    <tr>
                        <th class="text-muted">Statut</th>
                        <td>
                            <span class="badge text-bg-<?= $agent['actif'] ? 'success' : 'secondary' ?>">
                                <?= $agent['actif'] ? 'Actif' : 'Désactivé' ?>
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Changement de mot de passe -->
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header"><i class="bi bi-shield-lock"></i> Changer mon mot de passe</div>
            <div class="card-body">
                <form method="post" action="<?= url('profil', 'password') ?>">
                    <?= csrf_field() // jeton anti-CSRF obligatoire sur tout POST ?>

                    <div class="mb-3">
                        <label class="form-label">Mot de passe actuel</label>
                        <input type="password" name="ancien_mdp" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nouveau mot de passe</label>
                        <input type="password" name="nouveau_mdp" class="form-control" required
                               minlength="8" placeholder="8 caractères minimum">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirmer le nouveau mot de passe</label>
                        <input type="password" name="confirm_mdp" class="form-control" required minlength="8">
                    </div>
                    <button type="submit" class="btn btn-mediatheque">
                        <i class="bi bi-check-lg"></i> Modifier
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
