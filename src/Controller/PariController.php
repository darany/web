<?php

namespace App\Controller;

use App\Repository\RencontreRepository;
use App\Repository\PariRepository;
use App\Repository\EquipeRepository;
use App\Entity\Pari;
use App\Entity\Rencontre;
use App\Form\PariType;
use App\Form\PariMultipleType;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PariController extends AbstractController
{
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

    #[Route('/pari/rencontres', name: 'app_pari_rencontres')]
    public function rencontres(Request $request, EntityManagerInterface $entityManager,
             PariRepository $pariRepository, RencontreRepository $rencontreRepository, 
             EquipeRepository $equipeRepository): Response
    {
        //On doit être authentifié pour parier
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();
        
        $form = $this->createForm(PariMultipleType::class);
        $form->handleRequest($request);
        // && $form->isValid()
        if ($form->isSubmitted()) {
            dd($form);
            $pariForm = $form->getData();
            

        } else {
            $rencontres = $rencontreRepository->toutesLesRencontres();
            return $this->render('pari/rencontres.html.twig', [
                'form' => $form,
                'rencontres' => $rencontres,
            ]);
        }
        
    }
}
