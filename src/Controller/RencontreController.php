<?php

namespace App\Controller;

use App\Entity\Rencontre;
use App\Form\RencontreType;
use App\ApiResource\ApiRencontre;

use App\Repository\RencontreRepository;
use App\Service\GestionRencontre;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
class RencontreController extends AbstractController
{
    public function __construct(
        private RencontreRepository $rencontreRepository,
        private EntityManagerInterface $entityManager
    ) {}

    /**
     * Liste les matches
     *
     * @param RencontreRepository $rencontreRepository
     * @return Response
     */
    #[Route('/rencontres', name: 'app_rencontre')]
    public function index(): Response
    {
        $rencontres = $this->rencontreRepository->toutesLesRencontres();
        return $this->render('rencontre/index.html.twig', [
            'rencontres' => $rencontres,
        ]);
    }

    /**
     * Détail d'un match
     */
    #[Route('/rencontres/{id}', name: 'app_show_rencontre')]
    public function show(int $id): Response
    {
        $rencontre = $this->rencontreRepository->findRencontreById($id);
        return $this->render('rencontre/show.html.twig', [
            'rencontre' => $rencontre,
        ]);
    }

    /**
     * Liste les matches, mais à des fins d'administration
     *
     * @param RencontreRepository $rencontreRepository
     * @return Response
     */
    #[Route('/admin/rencontres', name: 'app_admin_rencontres')]
    public function adminIndex(): Response
    {
        $rencontres = $this->rencontreRepository->toutesLesRencontres();
        return $this->render('rencontre/admin.index.html.twig', [
            'rencontres' => $rencontres,
        ]);
    }

    /**
     * Modifier un match
     */
    #[Route('/admin/rencontres/{id}', name: 'app_edit_rencontre')]
    public function edit(int $id, Request $request): Response
    {
        $rencontre = $this->rencontreRepository->findOneById($id);
        if (!$rencontre) {
            throw $this->createNotFoundException('Match non trouvé !');
        }
        $form = $this->createForm(RencontreType::class, $rencontre);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $rencontre = $form->getData();
            $this->entityManager->persist($rencontre);
            $this->entityManager->flush();
            $this->addFlash('success', "Le match a été modifié avec succès.");
            return $this->redirectToRoute('app_admin_rencontres');
        }

        return $this->render('rencontre/edit.html.twig', [
            'rencontre' => $rencontre,
            'form' => $form,
        ]);
    }

    /**
     * Créer un match
     */
    #[Route('/admin/rencontres/create', name: 'app_create_rencontre', priority: 1)]
    public function create(Request $request): Response
    {
        $rencontre = new Rencontre();
        $form = $this->createForm(RencontreType::class, $rencontre);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $rencontre = $form->getData();
                $this->entityManager->persist($rencontre);
                $this->entityManager->flush();
                $this->addFlash('success', "Le match a été créé avec succès.");
                return $this->redirectToRoute('app_admin_rencontres');
            } else  {
                $errors = $form->getErrors();
                dd($errors);
                $this->addFlash('error', "Le match n'a pas pu être créé.");
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }

        return $this->render('rencontre/create.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * Supprimer un match
     */
    #[Route('/admin/rencontres/{id}/delete', name: 'app_delete_rencontre')]
    public function delete(int $id): Response
    {
        $rencontre = $this->rencontreRepository->findOneById($id);
        if (!$rencontre) {
            throw $this->createNotFoundException('Match non trouvé !');
        }

        //Il n'est pas possible de supprimer une rencontre si elle est commencée ou terminée
        $rencontre = $this->rencontreRepository->findOneById($id);
        if ($rencontre->getEtat() != Rencontre::ETAT_A_VENIR) {
            $this->addFlash('error', "Le match ne peut pas être supprimé car il est commencé ou terminé.");
            return $this->redirectToRoute('app_admin_rencontres');
        }

        //Il n'est pas possible de supprimer une rencontre si des paris ont été faits sur elle
        $pariRepository = $this->entityManager->getRepository(Pari::class);
        if ($pariRepository->countAllParisForEquipeId($id) > 0) {
            $this->addFlash('error', "Le match ne peut pas être supprimé car des paris ont été faits sur lui.");
            return $this->redirectToRoute('app_admin_rencontres');
        }
        
        $this->entityManager->remove($rencontre);
        $this->entityManager->flush();
        $this->addFlash('success', "Le match a été supprimé avec succès.");
        return $this->redirectToRoute('app_admin_rencontres');
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
