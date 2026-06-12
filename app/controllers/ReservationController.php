<?php
/**
 * Réservation des espaces (salles de coworking) : un adhérent réserve
 * une salle sur un créneau horaire. Les conflits de créneau sont
 * bloqués par un trigger MySQL.
 */
class ReservationController extends Controller
{
    /** Liste de toutes les réservations. */
    public function index($id = null): void
    {
        $this->view('reservation/index', [
            'titre'        => 'Réservation d\'espaces',
            'reservations' => $this->model('Reservation')->allDetailed(),
        ]);
    }

    /** Formulaire de nouvelle réservation. */
    public function form($id = null): void
    {
        $this->view('reservation/form', [
            'titre'     => 'Nouvelle réservation',
            'adherents' => $this->model('Adherent')->allWithAbonnement(),
            'salles'    => $this->model('Salle')->allSorted(),
        ]);
    }

    /** Enregistre une nouvelle réservation. */
    public function save($id = null): void
    {
        // Adhérent et salle sont obligatoires
        $adherentId = (int) ($_POST['adherent_id'] ?? 0);
        $salleId    = (int) ($_POST['salle_id']    ?? 0);
        if (!$adherentId || !$salleId) {
            $this->flash('Veuillez sélectionner un adhérent et une salle.', 'danger');
            $this->redirect('reservation', 'form');
        }

        $data = [
            'adherent_id'      => $adherentId,
            'salle_id'         => $salleId,
            'date_reservation' => ($_POST['date_reservation'] ?? '') ?: date('Y-m-d'),
            'heure_debut'      => ($_POST['heure_debut']      ?? '') ?: '09:00',
            'heure_fin'        => ($_POST['heure_fin']        ?? '') ?: '10:00',
        ];

        // Cohérence du créneau : la comparaison de chaînes "HH:MM"
        // fonctionne car le format trie naturellement les heures.
        if ($data['heure_fin'] <= $data['heure_debut']) {
            $this->flash('L\'heure de fin doit être postérieure à l\'heure de début.', 'danger');
            $this->redirect('reservation', 'form');
        }
        if ($data['date_reservation'] < date('Y-m-d')) {
            $this->flash('La date de réservation ne peut pas être dans le passé.', 'danger');
            $this->redirect('reservation', 'form');
        }

        try {
            $this->model('Reservation')->create($data);
            $this->flash('Réservation confirmée.');
        } catch (PDOException $e) {
            error_log('[Mediatheque] Reservation save: ' . $e->getMessage());
            // Si le refus vient d'un trigger métier (code SQL 45000,
            // ex : créneau déjà pris), son message est affichable tel quel.
            $msg = $e->getCode() === '45000' ? $e->getMessage() : 'La réservation n\'a pas pu être enregistrée.';
            $this->flash('Réservation refusée : ' . $msg, 'danger');
        }
        $this->redirect('reservation');
    }

    /** Annule une réservation (la salle redevient libre sur ce créneau). */
    public function annuler($id = null): void
    {
        if ($id) {
            $this->model('Reservation')->annuler($id);
            $this->flash('Réservation annulée.');
        }
        $this->redirect('reservation');
    }

    /** Supprime définitivement une réservation de l'historique. */
    public function delete($id = null): void
    {
        if ($id) {
            $this->model('Reservation')->delete($id);
            $this->flash('Réservation supprimée.');
        }
        $this->redirect('reservation');
    }
}
