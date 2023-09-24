<?php

namespace App\State;

use App\Repository\PariRepository;
use App\ApiResource\ApiPari;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PariStateProvider implements ProviderInterface
{
    public function __construct(
        private PariRepository $pariRepository,
        private Security $security,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $user = $this->security->getUser();
        if ($user === null) {
            throw new AuthenticationException('Non authentifié ou token invalide.');
        }
        $pariDb = $this->pariRepository->findOneByRencontreIdAndUserId($uriVariables['id'], $user->getId());
        if (!$pariDb) {
            throw new NotFoundHttpException('Pari non trouvé.');
        } else {
            $pari = new ApiPari();
            $pari->id = $pariDb->getId();
            $pari->mise = $pariDb->getMise();
            $pari->gain = $pariDb->getGain();
            $pari->nomEquipe = $pariDb->getEquipe()->getNom();
            $pari->owner = $pariDb->getUser();
            return $pari;
        }
    }
}
