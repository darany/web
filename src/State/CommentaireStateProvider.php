<?php

namespace App\State;

use App\Repository\RencontreRepository;
use App\ApiResource\ApiCommentaire;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CommentaireStateProvider implements ProviderInterface
{
    public function __construct(
        private RencontreRepository $rencontreRepository,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $commentaires = [];
        $rencontre = $this->rencontreRepository->findRencontreById($uriVariables['id']);
        if (!$rencontre) {
            throw new NotFoundHttpException('Rencontre non trouvée.');
        } else {
            foreach ($rencontre->getCommentaires() as $commentaireDb) {
                $commentaire = new ApiCommentaire();
                $commentaire->id = $commentaireDb->getId();
                $time = $rencontre->getHeureDebut()->diff($commentaireDb->getDateHeure())->i;
                $timedCommentaire = $time . 'm ' . $commentaireDb->getTexte();
                $commentaire->scoreEquipeA = $rencontre->getScoreEquipeA();
                $commentaire->scoreEquipeB = $rencontre->getScoreEquipeB();
                $commentaire->texte = $commentaireDb->getTexte();
                $commentaire->rencontreId = $rencontre->getId();
                $commentaire->texteDate = $timedCommentaire;
                $commentaires[] = $commentaire;
            }
            return $commentaires;
        }
    }
}
