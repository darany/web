<?php

namespace App\Controller;

use App\Entity\Joueur;
use App\Form\JoueurType;
use App\Repository\JoueurRepository;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class JoueurController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private JoueurRepository $joueurRepository
        ) {}

    #[Route('/admin/joueurs', name: 'app_joueurs')]
    public function index(): Response
    {
        $joueurs = $this->joueurRepository->findAll();
        return $this->render('joueur/index.html.twig', [
            'joueurs' => $joueurs,
        ]);
    }

    #[Route('/admin/joueurs/{id}', name: 'app_edit_joueur')]
    public function edit(int $id, Request $request): Response
    {
        $joueur = $this->joueurRepository->findOneById($id);
        if (!$joueur) {
            throw $this->createNotFoundException('Joueur non trouvée !');
        }
        $form = $this->createForm(JoueurType::class, $joueur);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $joueur = $form->getData();
            $this->entityManager->persist($joueur);
            $this->entityManager->flush();
            $this->addFlash('success', "Le joueur a été modifié avec succès.");
            return $this->redirectToRoute('app_joueurs');
        }

        return $this->render('joueur/edit.html.twig', [
            'joueur' => $joueur,
            'form' => $form,
        ]);
    }

    #[Route('/admin/joueurs/create', name: 'app_create_joueur', priority: 1)]
    public function create(Request $request): Response
    {
        $joueur = new Joueur();
        $form = $this->createForm(JoueurType::class, $joueur);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $joueur = $form->getData();
            $this->entityManager->persist($joueur);
            $this->entityManager->flush();
            $this->addFlash('success', "Le joueur a été créé avec succès.");
            return $this->redirectToRoute('app_joueurs');
        }

        return $this->render('joueur/create.html.twig', [
            'form' => $form,
        ]);
    }


    #[Route('/admin/joueurs/{id}/delete', name: 'app_delete_joueur')]
    public function delete(int $id): Response
    {
        $joueur = $this->joueurRepository->findOneById($id);
        if (!$joueur) {
            throw $this->createNotFoundException('Joueur non trouvé !');
        }
        
        $this->entityManager->remove($joueur);
        $this->entityManager->flush();
        $this->addFlash('success', "Le joueur a été supprimé avec succès.");
        return $this->redirectToRoute('app_joueurs');
    }
}
