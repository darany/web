<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use Symfony\Component\Serializer\Annotation\Groups;
use \DateTime;
use ApiPlatform\Metadata\ApiProperty;

use App\Repository\RencontreRepository;
use App\Controller\RencontreController;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RencontreRepository::class)]
#[ApiResource(
    description: 'Match du superbowl',
    operations: [
        new Get(normalizationContext: ['groups' => 'rencontre:item']),
        new GetCollection(normalizationContext: ['groups' => 'rencontre:list']),
        new Get(
            name: 'cloturer', 
            description: 'Cloturer un match et calculer les gains des paris',
            uriTemplate: '/rencontre/{id}/cloturer', 
            controller: RencontreController::class,
            security: "is_granted('ROLE_COMMENTATEUR')",
        )
    ],
    order: ['heureDebut' => 'DESC'],
    paginationEnabled: false,
)]
class Rencontre
{
    //Le match peut être « Termine », « À venir », « En Cours » 
    public const STATUT_A_VENIR = 0;
    public const STATUT_EN_COURS = 1;
    public const STATUT_TERMINE = 2;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['rencontre:list', 'rencontre:item'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['rencontre:list', 'rencontre:item'])]
    private ?\DateTimeInterface $heureDebut = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['rencontre:list', 'rencontre:item'])]
    private ?\DateTimeInterface $heureFin = null;

    #[ORM\Column]
    #[Groups(['rencontre:list', 'rencontre:item'])]
    private ?int $statut = null;

    #[ORM\Column]
    #[Groups(['rencontre:list', 'rencontre:item'])]
    private ?int $scoreEquipeA = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['rencontre:list', 'rencontre:item'])]
    private ?int $scoreEquipeB = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['rencontre:list', 'rencontre:item'])]
    private ?string $meteo = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[Groups(['rencontre:list', 'rencontre:item'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Equipe $equipeA = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[Groups(['rencontre:list', 'rencontre:item'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Equipe $equipeB = null;

    #[ORM\Column]
    #[Groups(['rencontre:list', 'rencontre:item'])]
    private ?float $coteEquipeA = null;

    #[ORM\Column]
    #[Groups(['rencontre:list', 'rencontre:item'])]
    private ?float $coteEquipeB = null;

    #[ORM\OneToMany(mappedBy: 'rencontre', targetEntity: Commentaire::class, orphanRemoval: true)]
    private Collection $commentaires;

    #[ORM\OneToMany(mappedBy: 'rencontre', targetEntity: Pari::class, orphanRemoval: false)]
    private Collection $paris;

    public function __construct()
    {
        $this->commentaires = new ArrayCollection();
        $this->paris = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Retourne le total des mises sur la rencontre
     * TODO : #[ApiProperty(security: "is_granted('ROLE_COMMENTATEUR')")]
     * @return float
     */
    #[Groups('rencontre:item')]
    public function getTotalDesMises(): float
    {
        return $this->paris->map(function ($paris) {
                return $paris->getMise(); 
            })->reduce(function(float $accumulator, float $value): float {
                return $accumulator + $value;
            }, 0);
    }

    /**
     * Retourne les commentaires de la rencontre avec l'heure du commentaire
     * relative au début de la rencontre (en minutes depuis le début)
     * @return float
     */
    #[Groups('rencontre:item')]
    public function getTimedCommentaires(): ?array
    {
        return $this->commentaires->map(function ($commentaire) {
            $time = $this->heureDebut->diff($commentaire->getDateHeure())->i;
            return $time . 'm ' . $commentaire->getTexte(); 
        })->toArray();
    }

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

    public function setHeureDebut(\DateTimeInterface $heureDebut): static
    {
        $this->heureDebut = $heureDebut;

        return $this;
    }

    public function getHeureFin(): ?\DateTimeInterface
    {
        return $this->heureFin;
    }

    public function setHeureFin(\DateTimeInterface $heureFin): static
    {
        $this->heureFin = $heureFin;

        return $this;
    }

    public function getStatut(): ?int
    {
        return $this->statut;
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

    public function isTerminee(): bool
    {
        return $this->statut == self::STATUT_TERMINE;
    }

    public function isAvenir(): bool
    {
        return $this->statut == self::STATUT_A_VENIR;
    }

    public function setStatut(int $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    public function getDisplayableScores(): ?string
    {
        if ($this->statut == self::STATUT_A_VENIR) {
            return '—';
        } else {
            return $this->scoreEquipeA . ' - ' . $this->scoreEquipeB;
        }
    }

    public function getScoreEquipeA(): ?int
    {
        return $this->scoreEquipeA;
    }

    public function setScoreEquipeA(int $scoreEquipeA): static
    {
        $this->scoreEquipeA = $scoreEquipeA;

        return $this;
    }

    public function getScoreEquipeB(): ?int
    {
        return $this->scoreEquipeB;
    }

    public function setScoreEquipeB(?int $scoreEquipeB): static
    {
        $this->scoreEquipeB = $scoreEquipeB;

        return $this;
    }

    public function getMeteo(): ?string
    {
        return $this->meteo;
    }

    public function setMeteo(?string $meteo): static
    {
        $this->meteo = $meteo;

        return $this;
    }

    public function getEquipeA(): ?Equipe
    {
        return $this->equipeA;
    }

    public function setEquipeA(Equipe $equipeA): static
    {
        $this->equipeA = $equipeA;

        return $this;
    }

    public function getEquipeB(): ?Equipe
    {
        return $this->equipeB;
    }

    public function setEquipeB(Equipe $equipeB): static
    {
        $this->equipeB = $equipeB;

        return $this;
    }

    public function getCoteEquipeA(): ?float
    {
        return $this->coteEquipeA;
    }

    public function setCoteEquipeA(float $coteEquipeA): static
    {
        $this->coteEquipeA = $coteEquipeA;

        return $this;
    }

    public function getCoteEquipeB(): ?float
    {
        return $this->coteEquipeB;
    }

    public function setCoteEquipeB(float $coteEquipeB): static
    {
        $this->coteEquipeB = $coteEquipeB;

        return $this;
    }

    /**
     * @return Collection<int, Commentaire>
     */
    public function getCommentaires(): Collection
    {
        return $this->commentaires;
    }

    public function addCommentaire(Commentaire $commentaire): static
    {
        if (!$this->commentaires->contains($commentaire)) {
            $this->commentaires->add($commentaire);
            $commentaire->setRencontre($this);
        }

        return $this;
    }

    public function removeCommentaire(Commentaire $commentaire): static
    {
        if ($this->commentaires->removeElement($commentaire)) {
            // set the owning side to null (unless already changed)
            if ($commentaire->getRencontre() === $this) {
                $commentaire->setRencontre(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Pari>
     */
    public function getParis(): Collection
    {
        return $this->paris;
    }

    public function addPari(Pari $pari): static
    {
        if (!$this->paris->contains($pari)) {
            $this->paris->add($pari);
            $pari->setRencontre($this);
        }

        return $this;
    }
}
