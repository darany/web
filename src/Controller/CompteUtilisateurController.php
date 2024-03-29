<?php

namespace App\Controller;

use App\Repository\PariRepository;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CompteUtilisateurController extends AbstractController
{
    /**
     * Permet l'affichage de la page de compte utilisateur
     * Cette page a trois vrais onglets calculés ici
     *
     * @param ChartBuilderInterface $chartBuilder
     * @param PariRepository $pariRepository
     * @return Response
     */
    #[Route('/compte/utilisateur', name: 'app_compte_utilisateur')]
    public function index(ChartBuilderInterface $chartBuilder, PariRepository $pariRepository): Response
    {
        //On doit être authentifié pour accèder à son compte
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();

        $paris = $pariRepository->findParisTerminesByUserId($user->getId());
        $paris = new ArrayCollection($paris);      
        $chart = $chartBuilder->createChart(Chart::TYPE_BAR);
        $data = [
            'labels' => $paris->map(function ($paris) { return $paris->getDate()->format('d/m/Y'); })->toArray(),
            'datasets' => [
                [
                    'label' => 'Gains',
                    'backgroundColor' => 'rgb(255, 99, 132)',
                    'borderColor' => 'rgb(255, 99, 132)',
                    'data' => $paris->map(function ($paris) { return $paris->getGain(); })->toArray(),
                ],
            ],
        ];
        $chart->setData($data);

        $tousLesParis = $pariRepository->findParisByUserId($user->getId());
        $tokenProvider = $this->container->get('security.csrf.token_manager');
        $token = $tokenProvider->getToken('delete-pari')->getValue();
        
        return $this->render('compte_utilisateur/index.html.twig', [
            'paris' => $tousLesParis,
            'user' => $user,
            'chart' => $chart,
            'token' => $token
        ]);
    }
}
