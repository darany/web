<?php

namespace App\Controller;

use App\Entity\Rencontre;
use App\ApiResource\ApiRencontre;

use App\Repository\RencontreRepository;
use App\Service\GestionRencontre;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
class RencontreController extends AbstractController
{
    /**
     * Lidte les matches
     *
     * @param RencontreRepository $rencontreRepository
     * @return Response
     */
    #[Route('/rencontres', name: 'app_rencontre')]
    public function index(RencontreRepository $rencontreRepository): Response
    {
        $rencontres = $rencontreRepository->toutesLesRencontres();
        return $this->render('rencontre/index.html.twig', [
            'rencontres' => $rencontres,
        ]);
    }

    /**
     * Détail d'un match
     */
    #[Route('/rencontres/{id}', name: 'app_show_rencontre')]
    public function show(RencontreRepository $rencontreRepository, int $id): Response
    {
        $rencontre = $rencontreRepository->findRencontreById($id);
        return $this->render('rencontre/show.html.twig', [
            'rencontre' => $rencontre,
        ]);
    }

    /**
     * Action personnalisé de l'API
     * 
     * Clôture un match et calcule les gains des paris par Web Service
     * 
     * Un commentateur peut clore un match à l’issue de la rencontre, l’action est manuelle, 
     * car un match peut durer plus longtemps que le temps donné s’il y a eu prolongation. 
     * Si le temps a été dépassé, l’action de « clore » un match change l’heure de fin de celui-ci.
     * La fermeture d’un match calcule le montant gagné ou perdu par l’utilisateur en fonction de son pari.
     *
     * @param Rencontre $rencontre
     * @param GestionRencontre $gestionRencontre
     * @return Rencontre
     */
    public function __invoke(Rencontre $rencontre, GestionRencontre $gestionRencontre): ApiRencontre
    {
        $rencontre = $gestionRencontre->cloturer($rencontre);
        return $rencontre->toApiRencontre();
    }
}
