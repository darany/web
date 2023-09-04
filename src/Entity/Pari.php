<?php

namespace App\Entity;

use App\Repository\PariRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PariRepository::class)]
class Pari
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $Date = null;

    #[ORM\ManyToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Rencontre $Rencontre = null;

    #[ORM\ManyToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Equipe $Equipe = null;

    #[ORM\Column]
    private ?float $Mise = null;

    #[ORM\Column(nullable: true)]
    private ?float $Gain = null;

    #[ORM\ManyToOne(inversedBy: 'paris')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->Date;
    }

    public function setDate(\DateTimeInterface $Date): static
    {
        $this->Date = $Date;

        return $this;
    }

    public function getRencontre(): ?Rencontre
    {
        return $this->Rencontre;
    }

    public function setRencontre(Rencontre $Rencontre): static
    {
        $this->Rencontre = $Rencontre;

        return $this;
    }

    public function getEquipe(): ?Equipe
    {
        return $this->Equipe;
    }

    public function setEquipe(Equipe $Equipe): static
    {
        $this->Equipe = $Equipe;

        return $this;
    }

    public function getMise(): ?float
    {
        return $this->Mise;
    }

    public function setMise(float $Mise): static
    {
        $this->Mise = $Mise;

        return $this;
    }

    public function getGain(): ?float
    {
        return $this->Gain;
    }

    public function setGain(?float $Gain): static
    {
        $this->Gain = $Gain;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
