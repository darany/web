<?php

namespace App\Controller;

use App\Repository\RencontreRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RencontreController extends AbstractController
{
    #[Route('/rencontre', name: 'app_rencontre')]
    public function index(RencontreRepository $rencontreRepository): Response
    {
        $rencontres = $rencontreRepository->toutesLesRencontres();
        return $this->render('rencontre/index.html.twig', [
            'rencontres' => $rencontres,
        ]);
    }

    #[Route('/rencontre/{id}', name: 'app_show_rencontre')]
    public function show(RencontreRepository $rencontreRepository, int $id): Response
    {
        $rencontre = $rencontreRepository->findRencontreById($id);
        return $this->render('rencontre/show.html.twig', [
            'rencontre' => $rencontre,
        ]);
    }
}
