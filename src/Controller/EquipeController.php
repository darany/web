<?php

namespace App\Controller;

use App\Entity\Equipe;
use App\Entity\Rencontre;
use App\Entity\Pari;
use App\Form\EquipeType;
use App\Repository\EquipeRepository;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class EquipeController extends AbstractController
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private EquipeRepository $equipeRepository
        ) {}

    #[Route('/admin/equipes', name: 'app_equipes')]
    public function index(): Response
    {
        $equipes = $this->equipeRepository->findAll();
        return $this->render('equipe/index.html.twig', [
            'equipes' => $equipes,
        ]);
    }

    #[Route('/admin/equipes/{id}', name: 'app_edit_equipe')]
    public function edit(int $id, Request $request): Response
    {
        $equipe = $this->equipeRepository->findOneById($id);
        if (!$equipe) {
            throw $this->createNotFoundException('Équipe non trouvée !');
        }
        $form = $this->createForm(EquipeType::class, $equipe);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $equipe = $form->getData();
            $this->entityManager->persist($equipe);
            $this->entityManager->flush();
            $this->addFlash('success', "L'équipe a été modifiée avec succès.");
            return $this->redirectToRoute('app_equipes');
        }

        return $this->render('equipe/edit.html.twig', [
            'equipe' => $equipe,
            'form' => $form,
        ]);
    }

    #[Route('/admin/equipes/create', name: 'app_create_equipe', priority: 1)]
    public function create(Request $request): Response
    {
        $equipe = new Equipe();
        $form = $this->createForm(EquipeType::class, $equipe);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $equipe = $form->getData();
            $this->entityManager->persist($equipe);
            $this->entityManager->flush();
            $this->addFlash('success', "L'équipe a été créée avec succès.");
            return $this->redirectToRoute('app_equipes');
        }

        return $this->render('equipe/create.html.twig', [
            'form' => $form,
        ]);
    }


    #[Route('/admin/equipes/{id}/delete', name: 'app_delete_equipe')]
    public function delete(int $id): Response
    {
        $equipe = $this->equipeRepository->findOneById($id);
        if (!$equipe) {
            throw $this->createNotFoundException('Équipe non trouvée !');
        }

        //Il n'est pas possible de supprimer une équipe si elle possède des joueurs
        if (count($equipe->getJoueurs()) > 0) {
            $this->addFlash('error', "L'équipe ne peut pas être supprimée car elle possède des joueurs.");
            return $this->redirectToRoute('app_equipes');
        }

        //Il n'est pas possible de supprimer une équipe si elle a joué un match
        $rencontreRepository = $this->entityManager->getRepository(Rencontre::class);
        if ($rencontreRepository->countAllRencontresForEquipeId($id) > 0) {
            $this->addFlash('error', "L'équipe ne peut pas être supprimée car elle a joué un match.");
            return $this->redirectToRoute('app_equipes');
        }

        //Il n'est pas possible de supprimer une équipe si des paris ont été faits sur elle
        $pariRepository = $this->entityManager->getRepository(Pari::class);
        if ($pariRepository->countAllParisForEquipeId($id) > 0) {
            $this->addFlash('error', "L'équipe ne peut pas être supprimée car des paris ont été faits sur elle.");
            return $this->redirectToRoute('app_equipes');
        }
        
        $this->entityManager->remove($equipe);
        $this->entityManager->flush();
        $this->addFlash('success', "L'équipe a été supprimée avec succès.");
        return $this->redirectToRoute('app_equipes');
    }
}
