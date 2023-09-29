<?php

namespace App\Controller;

use App\Entity\Equipe;
use App\Form\EquipeType;
use App\Repository\EquipeRepository;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EquipeController extends AbstractController
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private EquipeRepository $equipeRepository
        ) {}

    #[Route('/admin/equipe', name: 'app_equipe')]
    public function index(): Response
    {
        $equipes = $this->equipeRepository->findAll();
        return $this->render('equipe/index.html.twig', [
            'equipes' => $equipes,
        ]);
    }

    #[Route('/admin/equipe/{id}', name: 'app_show_equipe')]
    public function show(int $id): Response
    {
        $equipe = $this->equipeRepository->findOneById($id);
        if (!$equipe) {
            throw $this->createNotFoundException('Équipe non trouvée !');
        }
        return $this->render('equipe/show.html.twig', [
            'equipe' => $equipe,
        ]);
    }
}
