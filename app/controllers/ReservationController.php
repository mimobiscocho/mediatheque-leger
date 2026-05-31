<?php
/** Réservation des espaces (salles de coworking). */
class ReservationController extends Controller
{
    public function index($id = null): void
    {
        $this->view('reservation/index', [
            'titre'        => 'Réservation d\'espaces',
            'reservations' => $this->model('Reservation')->allDetailed(),
        ]);
    }

    public function form($id = null): void
    {
        $this->view('reservation/form', [
            'titre'     => 'Nouvelle réservation',
            'adherents' => $this->model('Adherent')->allWithAbonnement(),
            'salles'    => $this->model('Salle')->allSorted(),
        ]);
    }

    public function save($id = null): void
    {
        $adherentId = (int) ($_POST['adherent_id'] ?? 0);
        $salleId    = (int) ($_POST['salle_id'] ?? 0);
        if (!$adherentId || !$salleId) {
            $this->flash('Veuillez sélectionner un adhérent et une salle.', 'danger');
            $this->redirect('reservation', 'form');
        }

        $data = [
            'adherent_id'      => $adherentId,
            'salle_id'         => $salleId,
            'date_reservation' => $_POST['date_reservation'] ?: date('Y-m-d'),
            'heure_debut'      => $_POST['heure_debut'] ?: '09:00',
            'heure_fin'        => $_POST['heure_fin'] ?: '10:00',
        ];

        if ($data['heure_fin'] <= $data['heure_debut']) {
            $this->flash('L\'heure de fin doit être postérieure à l\'heure de début.', 'danger');
            $this->redirect('reservation', 'form');
        }

        try {
            $this->model('Reservation')->create($data);
            $this->flash('Réservation confirmée.');
        } catch (PDOException $e) {
            // Message remonté par le trigger trg_reservation_before_insert
            $this->flash('Réservation refusée : ' . $e->getMessage(), 'danger');
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

    public function delete($id = null): void
    {
        if ($id) {
            $this->model('Reservation')->delete($id);
            $this->flash('Réservation supprimée.');
        }
        $this->redirect('reservation');
    }
}
