<?php

namespace App\State;

use App\Repository\RencontreRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\ApiResource\ApiCommentaire;
use App\Entity\Commentaire;

use ApiPlatform\Metadata\PostOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class CommentaireStateProcessor implements ProcessorInterface
{
    public function __construct(
        private RencontreRepository $rencontreRepository,
        private Security $security,
        private EntityManagerInterface $entityManager,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $user = $this->security->getUser();
        if ($user === null) {
            throw new AuthenticationException('Non authentifié ou token invalide.');
        }
        
        $rencontre = $this->rencontreRepository->findRencontreById($data->rencontreId);
        if (!$rencontre) {
            throw new NotFoundHttpException('Rencontre non trouvée.');
        } else {
            $commentaire = new Commentaire();
            $commentaire->setCommentateur($user);
            $commentaire->setDateHeure(new \DateTime());
            $commentaire->setTexte($data->texte);
            $rencontre->addCommentaire($commentaire);
            if (isset($data->scoreEquipeA)) {
                $rencontre->setScoreEquipeA($data->scoreEquipeA);
            }
            if (isset($data->scoreEquipeB)) {
                $rencontre->setScoreEquipeB($data->scoreEquipeB);
            }
            $this->entityManager->persist($rencontre);
            $this->entityManager->flush();

            $commentaireApi = new ApiCommentaire();
            $commentaireApi->id = $commentaire->getId();
            $commentaireApi->rencontreId = $rencontre->getId();
            $commentaireApi->scoreEquipeA = $rencontre->getScoreEquipeA();
            $commentaireApi->scoreEquipeB = $rencontre->getScoreEquipeB();
            $commentaireApi->texte = $commentaire->getTexte();
            return $commentaireApi;
        }
    }
}
