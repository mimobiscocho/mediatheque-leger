<?php
/**
 * Tableau de bord (page d'accueil) : rassemble des statistiques
 * et les dernières activités de tous les modules.
 */
class HomeController extends Controller
{
    public function index($id = null): void
    {
        $pret = $this->model('Pret');
        $resa = $this->model('Reservation');

        $this->view('home/index', [
            'titre'        => 'Tableau de bord',
            // Les 4 compteurs affichés en haut de page
            'stats'        => [
                'adherents' => $this->model('Adherent')->count(),
                'livres'    => $this->model('Livre')->count(),
                'materiels' => $this->model('Materiel')->count(),
                'salles'    => $this->model('Salle')->count(),
            ],
            'pretsEnCours' => $pret->countEnCours(),
            'resaActives'  => $resa->countActives(),
            'retards'      => $pret->enRetard(),
            // array_slice(..., 0, 5) : on ne garde que les 5 plus récents
            'prets'        => array_slice($pret->allDetailed(), 0, 5),
            'reservations' => array_slice($resa->allDetailed(), 0, 5),
        ]);
    }
}
