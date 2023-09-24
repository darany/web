<?php

namespace App\Controller;

use App\Repository\RencontreRepository;
use App\Repository\PariRepository;
use App\Repository\EquipeRepository;
use App\Entity\Pari;
use App\Entity\Rencontre;
use App\Form\PariType;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PariController extends AbstractController
{
    /**
     * Permet à un utilisateur de parier sur une rencontre
     * 
     * @param PariRepository $pariRepository
     * @return Response
     */
    #[Route('/pari/rencontre/{id}', name: 'app_pari_rencontre')]
    public function index(Request $request, EntityManagerInterface $entityManager,
             PariRepository $pariRepository, RencontreRepository $rencontreRepository, 
             EquipeRepository $equipeRepository ,int $id): Response
    {
        //On doit être authentifié pour parier
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();
        
        //Créer le pari s'il n'existe pas
        $pari = $pariRepository->findOneByRencontreIdAndUserId($id, $user->getId());
        if (is_null($pari)) {
            $pari = new Pari();
            $rencontre = $rencontreRepository->findRencontreById($id);
            if (is_null($rencontre)) throw $this->createNotFoundException('Rencontre non trouvée');
            $pari->setRencontre($rencontre);
            $pari->setUser($user);
            $pari->setMise(0);
        }

        // Cas d'un pari sur une rencontre en cours ou terminée
        if ($pari->getRencontre()->getStatut() != Rencontre::STATUT_A_VENIR) throw $this->createNotFoundException('Impossible de parier sur une rencontre en cours ou terminée');

        $form = $this->createForm(PariType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $pariForm = $form->getData();
            // Cas de la suppression du pari (mise à 0 d'un pari existant)
            $pariExistant = $pariRepository->findOneByRencontreIdAndUserId($id, $user->getId());
            if (!is_null($pariExistant)) {
                if ($pariForm['mise'] == 0) {
                $entityManager->remove($pari);
                $entityManager->flush();
                return $this->redirectToRoute('app_pari_rencontre', ['id' => $id]);                    
                }
            }
            // Cas de la création ou de la modification d'un pari
            $pari->setDate(new \DateTime());            
            $equipe = $equipeRepository->findEquipeById(intval($pariForm['equipe']));
            if (is_null($equipe)) throw $this->createNotFoundException('Equipe non trouvée');
            $pari->setEquipe($equipe);
            if ($pariForm['mise'] < 0) throw $this->createNotFoundException('Mise non valide');
            $pari->setMise(floatval($pariForm['mise']));
            $entityManager->persist($pari);
            $entityManager->flush();
            return $this->redirectToRoute('app_pari_rencontre', ['id' => $id]);
        } else {
            return $this->render('pari/index.html.twig', [
                'form' => $form,
                'pari' => $pari,
            ]);
        }
    }

    /**
     * Suppression d'un pari par appel Ajax
     * Le header doit contenir un token CSRF valide pour que la requête soit acceptée
     * Ce token est en fait généré par le contrôleur listant les paris passés (espace utilisateur)
     * @see \App\Controller\EspaceUtilisateurController::index()
     */
    #[Route('/pari/{id}', name: 'app_delete_pari', methods: ['DELETE'])]
    public function delete(Request $request, EntityManagerInterface $entityManager, PariRepository $pariRepository,int $id): Response
    {
        $response = new Response();
        $token = $request->headers->get('X-CSRF-TOKEN');
        if (!$this->isCsrfTokenValid('delete-pari', $token)) {
            $response->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
            return $response;
        }

        //On doit être authentifié pour supprimer un pari
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();
        
        // Vérifier que le pari existe
        $pari = $pariRepository->find($id);
        
        if (is_null($pari)) {
            throw $this->createNotFoundException('Le pari n\'existe pas');
        } else {
            // Vérifier que le pari appartient à l'utilisateur
            if ($pari->getUser() != $user) {
                throw $this->createNotFoundException('Le pari n\'appartient pas à l\'utilisateur');
            } else {
                $entityManager->remove($pari);
                $entityManager->flush();
                $response->setStatusCode(Response::HTTP_OK);
                return $response;
            }
        }
    }
}
