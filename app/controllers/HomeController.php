<?php
/** Tableau de bord : vues de synthèse sur l'ensemble des données. */
class HomeController extends Controller
{
    public function index($id = null): void
    {
        $pret = $this->model('Pret');
        $resa = $this->model('Reservation');

        $this->view('home/index', [
            'titre'        => 'Tableau de bord',
            'stats'        => [
                'adherents' => $this->model('Adherent')->count(),
                'livres'    => $this->model('Livre')->count(),
                'materiels' => $this->model('Materiel')->count(),
                'salles'    => $this->model('Salle')->count(),
            ],
            'pretsEnCours' => $pret->countEnCours(),
            'resaActives'  => $resa->countActives(),
            'retards'      => $pret->enRetard(),
            'prets'        => array_slice($pret->allDetailed(), 0, 5),
            'reservations' => array_slice($resa->allDetailed(), 0, 5),
        ]);
    }
}
