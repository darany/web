<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\RencontreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RencontreRepository::class)]
#[ApiResource]
class Rencontre
{
    //Le match peut être « Termine », « À venir », « En Cours » 
    public const STATUT_A_VENIR = 0;
    public const STATUT_EN_COURS = 1;
    public const STATUT_TERMINE = 2;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $HeureDebut = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $HeureFin = null;

    #[ORM\Column]
    private ?int $Statut = null;

    #[ORM\Column]
    private ?int $ScoreEquipeA = null;

    #[ORM\Column(nullable: true)]
    private ?int $ScoreEquipeB = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $Meteo = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Equipe $EquipeA = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Equipe $EquipeB = null;

    #[ORM\Column]
    private ?float $CoteEquipeA = null;

    #[ORM\Column]
    private ?float $CoteEquipeB = null;

    #[ORM\OneToMany(mappedBy: 'Rencontre', targetEntity: Commentaire::class, orphanRemoval: true)]
    private Collection $commentaires;

    #[ORM\OneToMany(mappedBy: 'Pari', targetEntity: Pari::class, orphanRemoval: false)]
    private Collection $paris;

    public function __construct()
    {
        $this->commentaires = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getJour(): ?string
    {
        $formatter = new \IntlDateFormatter('fr_FR', \IntlDateFormatter::LONG, \IntlDateFormatter::NONE);
        return $formatter->format($this->HeureDebut);
    }

    public function getHeureDebut(): ?\DateTimeInterface
    {
        return $this->HeureDebut;
    }

    public function setHeureDebut(\DateTimeInterface $HeureDebut): static
    {
        $this->HeureDebut = $HeureDebut;

        return $this;
    }

    public function getHeureFin(): ?\DateTimeInterface
    {
        return $this->HeureFin;
    }

    public function setHeureFin(\DateTimeInterface $HeureFin): static
    {
        $this->HeureFin = $HeureFin;

        return $this;
    }

    public function getStatut(): ?int
    {
        return $this->Statut;
    }

    public function getStatutString(): ?string
    {
        $statuses = [
            self::STATUT_A_VENIR => 'À venir',
            self::STATUT_EN_COURS => 'En cours',
            self::STATUT_TERMINE => 'Terminé'
        ];
        return $statuses[$this->Statut];
    }

    public function setStatut(int $Statut): static
    {
        $this->Statut = $Statut;
        return $this;
    }

    public function getDisplayableScores(): ?string
    {
        if ($this->Statut == self::STATUT_A_VENIR) {
            return '—';
        } else {
            return $this->ScoreEquipeA . ' - ' . $this->ScoreEquipeB;
        }
    }

    public function getScoreEquipeA(): ?int
    {
        return $this->ScoreEquipeA;
    }

    public function setScoreEquipeA(int $ScoreEquipeA): static
    {
        $this->ScoreEquipeA = $ScoreEquipeA;

        return $this;
    }

    public function getScoreEquipeB(): ?int
    {
        return $this->ScoreEquipeB;
    }

    public function setScoreEquipeB(?int $ScoreEquipeB): static
    {
        $this->ScoreEquipeB = $ScoreEquipeB;

        return $this;
    }

    public function getMeteo(): ?string
    {
        return $this->Meteo;
    }

    public function setMeteo(?string $Meteo): static
    {
        $this->Meteo = $Meteo;

        return $this;
    }

    public function getEquipeA(): ?Equipe
    {
        return $this->EquipeA;
    }

    public function setEquipeA(Equipe $EquipeA): static
    {
        $this->EquipeA = $EquipeA;

        return $this;
    }

    public function getEquipeB(): ?Equipe
    {
        return $this->EquipeB;
    }

    public function setEquipeB(Equipe $EquipeB): static
    {
        $this->EquipeB = $EquipeB;

        return $this;
    }

    public function getCoteEquipeA(): ?float
    {
        return $this->CoteEquipeA;
    }

    public function setCoteEquipeA(float $CoteEquipeA): static
    {
        $this->CoteEquipeA = $CoteEquipeA;

        return $this;
    }

    public function getCoteEquipeB(): ?float
    {
        return $this->CoteEquipeB;
    }

    public function setCoteEquipeB(float $CoteEquipeB): static
    {
        $this->CoteEquipeB = $CoteEquipeB;

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
