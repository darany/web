<?php

namespace App\State;

use App\Repository\RencontreRepository;
use ApiPlatform\Metadata\CollectionOperationInterface;
use App\ApiResource\ApiRencontre;
use \DateTime;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;

class RencontreStateProvider implements ProviderInterface
{
    public function __construct(
        private RencontreRepository $rencontreRepository,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        //TODO we should inject the custom filter here
        if ($operation instanceof CollectionOperationInterface) {
            //$rencontres = $this->collectionProvider->provide($operation, $uriVariables, $context);
            return $this->getListOfRencontres($context);
        }
        //$rencontre = $this->itemProvider->provide($operation, $uriVariables, $context);
        return $this->getOneRencontre($uriVariables['id']);
    }

    /**
     * Liste de toutes les rencontres
     *
     * @return array
     */
    private function getListOfRencontres($context): array
    {
        $rencontres = [];
        $dbRencontres = $this->rencontreRepository
                ->createQueryBuilder('Rencontre');
        if (isset($context['filters']['heureDebut'])) {
            if ($context['filters']['heureDebut'] != '') {
                $valueDate = DateTime::createFromFormat('Y-m-d', $context['filters']['heureDebut']);
                $dbRencontres->andWhere('Rencontre.heureDebut BETWEEN :dateMin AND :dateMax')
                ->setParameters(
                    [
                        'dateMin' => $valueDate->format('Y-m-d 00:00:00'),
                        'dateMax' => $valueDate->format('Y-m-d 23:59:59'),
                    ]
                );
            }
        }
        $dbRencontres->orderBy('Rencontre.heureDebut', 'DESC');
        $dbRencontres = $dbRencontres->getQuery()->getResult();

        foreach ($dbRencontres as $dbRencontre) {
            $rencontres[] = $dbRencontre->toApiRencontre();
        }
        return $rencontres;
    }

    /**
     * Une rencontre en particulier
     *
     * @param integer $id
     * @return ApiRencontre
     */
    private function getOneRencontre(int $id): ApiRencontre
    {
        $dbRencontre = $this->rencontreRepository->findRencontreById($id);
        if (!$dbRencontre) {
            throw new \Exception('Rencontre not found');
        } else {
            return $dbRencontre->toApiRencontre();            
        }
    }
}
