<?php

namespace App\Controller;

use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CompteUtilisateurController extends AbstractController
{
    #[Route('/compte/utilisateur', name: 'app_compte_utilisateur')]
    public function index(ChartBuilderInterface $chartBuilder): Response
    {
        $chart = $chartBuilder->createChart(Chart::TYPE_LINE);
        $chart->setData([
            'labels' => ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
            'datasets' => [
                [
                    'label' => 'Sales!',
                    'backgroundColor' => 'rgb(255, 99, 132)',
                    'borderColor' => 'rgb(255, 99, 132)',
                    'data' => [522, 1500, 2250, 2197, 2345, 3122, 3099],
                ],
            ],
        ]);

        return $this->render('compte_utilisateur/index.html.twig', [
            'controller_name' => 'CompteUtilisateurController',
            'chart' => $chart
        ]);
    }
}
