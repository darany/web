<?php

namespace App\Controller;

use App\Repository\RencontreRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomePageController extends AbstractController
{
    #[Route('/', name: 'app_home_page')]
    public function index(RencontreRepository $rencontreRepository): Response
    {
        $rencontres = $rencontreRepository->rencontresDuJour();
        return $this->render('home_page/index.html.twig', [
            'rencontres' => $rencontres,
        ]);
    }
}
