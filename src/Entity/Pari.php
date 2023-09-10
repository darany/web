<?php

namespace App\Entity;

use App\Repository\PariRepository;
use Symfony\Component\Validator\Constraints as Assert;
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
    private ?\DateTimeInterface $date = null;

    #[ORM\ManyToOne(inversedBy: 'paris')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Rencontre $rencontre = null;

    #[ORM\ManyToOne()]
    #[ORM\JoinColumn(nullable: false)]
    private ?Equipe $equipe = null;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    #[Assert\Type('float')]
    private ?float $mise = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Type('float')]
    private ?float $gain = null;

    #[ORM\ManyToOne(inversedBy: 'paris')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getRencontre(): ?Rencontre
    {
        return $this->rencontre;
    }

    public function setRencontre(Rencontre $rencontre): static
    {
        $this->rencontre = $rencontre;

        return $this;
    }

    public function getEquipe(): ?Equipe
    {
        return $this->equipe;
    }

    public function setEquipe(Equipe $equipe): static
    {
        $this->equipe = $equipe;

        return $this;
    }

    public function getMise(): ?float
    {
        return $this->mise;
    }

    public function setMise(float $mise): static
    {
        $this->mise = $mise;

        return $this;
    }

    public function getGain(): ?float
    {
        return $this->gain;
    }

    public function setGain(?float $gain): static
    {
        $this->gain = $gain;

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
