<?php
/**
 * Gestion des types d'abonnement.
 * La consultation est ouverte à tous les agents, mais la création,
 * la modification et la suppression sont réservées au rôle "admin".
 */
class AbonnementController extends Controller
{
    /** Liste des abonnements (triés par tarif croissant). */
    public function index($id = null): void
    {
        $this->view('abonnement/index', [
            'titre'       => 'Gestion des abonnements',
            'abonnements' => $this->model('Abonnement')->allSorted(),
        ]);
    }

    /** Formulaire de création (sans id) ou de modification (avec id). */
    public function form($id = null): void
    {
        // Seul un administrateur peut modifier les abonnements
        Security::requireRole('admin');

        $model = $this->model('Abonnement');
        $this->view('abonnement/form', [
            'titre'      => $id ? 'Modifier un abonnement' : 'Nouvel abonnement',
            'abonnement' => $id ? $model->find($id) : null,
        ]);
    }

    /** Enregistre le formulaire (création si pas d'id, sinon mise à jour). */
    public function save($id = null): void
    {
        Security::requireRole('admin');

        // Récupération et nettoyage des champs du formulaire.
        // max() évite les valeurs négatives ou nulles incohérentes.
        $data = [
            'libelle'        => Security::sanitize($_POST['libelle'] ?? ''),
            'tarif'          => max(0, (float) ($_POST['tarif'] ?? 0)),
            'duree_mois'     => max(1, (int) ($_POST['duree_mois'] ?? 12)),
            'quota_emprunts' => max(1, (int) ($_POST['quota_emprunts'] ?? 5)),
        ];

        if ($data['libelle'] === '') {
            $this->flash('Le libellé de l\'abonnement est obligatoire.', 'danger');
            $this->redirect('abonnement', 'form');
        }

        $model = $this->model('Abonnement');
        try {
            // Avec un id on modifie la ligne existante, sinon on en crée une
            $id ? $model->update($id, $data) : $model->create($data);
            Logger::info('Abonnement ' . ($id ? 'mis à jour' : 'ajouté') . ' : ' . $data['libelle']);
            $this->flash($id ? 'Abonnement mis à jour.' : 'Abonnement ajouté.');
        } catch (PDOException $e) {
            // L'erreur technique part dans le journal, l'utilisateur
            // ne voit qu'un message générique (pas de détail SQL).
            Logger::error('Abonnement save : ' . $e->getMessage());
            $this->flash('Enregistrement impossible : la donnée saisie est invalide.', 'danger');
        }
        $this->redirect('abonnement');
    }

    /** Supprime un abonnement. */
    public function delete($id = null): void
    {
        Security::requireRole('admin');

        if ($id) {
            try {
                $this->model('Abonnement')->delete($id);
                Logger::info("Abonnement supprimé : #$id");
                $this->flash('Abonnement supprimé.');
            } catch (PDOException $e) {
                // Cas typique : abonnement encore rattaché à des adhérents
                Logger::error('Abonnement delete : ' . $e->getMessage());
                $this->flash('Suppression impossible : cet abonnement est encore utilisé.', 'danger');
            }
        }
        $this->redirect('abonnement');
    }
}
