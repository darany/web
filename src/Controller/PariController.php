<?php

namespace App\Controller;

use App\Repository\RencontreRepository;
use App\Repository\PariRepository;
use App\Entity\User;
use App\Entity\Pari;
use App\Form\PariType;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PariController extends AbstractController
{
    #[Route('/pari/rencontre/{rencontreId}', name: 'app_pari_rencontre')]
    public function index(Request $request, ValidatorInterface $validator, PariRepository $pariRepository, RencontreRepository $rencontreRepository, int $rencontreId): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();

        $pari = new Pari();
        $form = $this->createForm(PariType::class, $pari);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $pari = $form->getData();

        } else {
            $rencontre = $rencontreRepository->findRencontreById($rencontreId);
            $pari = $pariRepository->findOneByRencontreIdAndUserId($rencontreId, $user->getId());
            return $this->render('pari/index.html.twig', [
                'rencontre' => $rencontre,
                'pari' => $pari,
            ]);
        }
    }
}
