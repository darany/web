<?php

namespace App\ApiResource;

use App\Filter\DateFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use App\State\RencontreStateProvider;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\Ignore;
use ApiPlatform\Metadata\ApiProperty;

use App\Controller\RencontreController;

#[ApiResource(
    shortName: 'Rencontre',
    description: 'Match du superbowl',
    provider: RencontreStateProvider::class,
    operations: [
        new Get(normalizationContext: ['groups' => 'rencontre:item']),
        new GetCollection(normalizationContext: ['groups' => 'rencontre:list']),
        new Get(
            name: 'cloturer', 
            description: 'Cloturer un match et calculer les gains des paris',
            uriTemplate: '/rencontres/{id}/cloturer', 
            controller: RencontreController::class,
            security: "is_granted('ROLE_COMMENTATEUR')",
        )
    ],
    order: ['heureDebut' => 'DESC'],
    paginationEnabled: false,
)]
#[ApiFilter(DateFilter::class, properties: ['heureDebut'])]
class ApiRencontre
{
    //Le match peut être « Terminé », « À venir », « En Cours » 
    private const STATUT_A_VENIR = 0;
    private const STATUT_EN_COURS = 1;
    private const STATUT_TERMINE = 2;

    public ?int $id = null;

    public ?\DateTimeInterface $heureDebut = null;

    public ?\DateTimeInterface $heureFin = null;

    #[Ignore]
    public ?int $statut = null;

    #[Groups(['rencontre:item'])]
    public ?int $scoreEquipeA = null;

    #[Groups(['rencontre:item'])]
    public ?int $scoreEquipeB = null;

    #[Groups(['rencontre:item'])]
    public ?string $meteo = null;

    #[Groups(['rencontre:list', 'rencontre:item'])]
    public ?string $equipeA = null;

    #[Groups(['rencontre:list', 'rencontre:item'])]
    public ?string $equipeB = null;

    #[Groups(['rencontre:list', 'rencontre:item'])]
    public ?float $coteEquipeA = null;

    #[Groups(['rencontre:list', 'rencontre:item'])]
    public ?float $coteEquipeB = null;

    #[ApiProperty(security: "is_granted('ROLE_COMMENTATEUR')")]
    #[Groups(['rencontre:item'])]
    public ?float $totalDesMises = 0.0;

    #[ApiProperty(security: "is_granted('ROLE_COMMENTATEUR')")]
    #[Groups(['rencontre:item'])]
    public ?int $nombreDeParisSurEquipeA = 0;

    #[ApiProperty(security: "is_granted('ROLE_COMMENTATEUR')")]
    #[Groups(['rencontre:item'])]
    public ?int $nombreDeParisSurEquipeB = 0;

    /**
     * Retourne le jour de la rencontre à partir de l'heure de début
     * Formatté en français
     *
     * @return string|null
     */
    #[Groups('rencontre:list', 'rencontre:item')]
    public function getJour(): ?string
    {
        $formatter = new \IntlDateFormatter('fr_FR', \IntlDateFormatter::LONG, \IntlDateFormatter::NONE);
        return $formatter->format($this->heureDebut);
    }

    /**
     * Retourne l'heure de début et de fin de la rencontre séparés par un tiret 
     * dans une chaîne de caractères
     * 
     * @return string|null
     */
    #[Groups('rencontre:list', 'rencontre:item')]
    public function getHoraire(): ?string
    {
        return $this->heureDebut->format('H:i') . ' - ' . $this->heureFin->format('H:i');
    }

    public function getHeureDebut(): ?\DateTimeInterface
    {
        return $this->heureDebut;
    }

    public function getHeureFin(): ?\DateTimeInterface
    {
        return $this->heureFin;
    }

    public function getStatutString(): ?string
    {
        $statuses = [
            self::STATUT_A_VENIR => 'À venir',
            self::STATUT_EN_COURS => 'En cours',
            self::STATUT_TERMINE => 'Terminé'
        ];
        return $statuses[$this->statut];
    }

    #[Groups(['rencontre:item'])]
    public function isEncours(): ?bool
    {
        return $this->statut == self::STATUT_EN_COURS;
    }

    #[Groups(['rencontre:item'])]
    public function isTerminee(): bool
    {
        return $this->statut == self::STATUT_TERMINE;
    }

    #[Groups(['rencontre:item'])]
    public function isAvenir(): bool
    {
        return $this->statut == self::STATUT_A_VENIR;
    }

    #[Groups(['rencontre:list', 'rencontre:item'])]
    public function getDisplayableScores(): ?string
    {
        if ($this->statut == self::STATUT_A_VENIR) {
            return '—';
        } else {
            return $this->scoreEquipeA . ' - ' . $this->scoreEquipeB;
        }
    }
}
