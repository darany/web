<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\CommentaireRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommentaireRepository::class)]
#[ApiResource]
class Commentaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $DateHeure = null;

    #[ORM\Column(length: 1024)]
    private ?string $Texte = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $Commentateur = null;

    #[ORM\ManyToOne(inversedBy: 'commentaires')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Rencontre $Rencontre = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateHeure(): ?\DateTimeInterface
    {
        return $this->DateHeure;
    }

    public function setDateHeure(\DateTimeInterface $DateHeure): static
    {
        $this->DateHeure = $DateHeure;

        return $this;
    }

    public function getTexte(): ?string
    {
        return $this->Texte;
    }

    public function setTexte(string $Texte): static
    {
        $this->Texte = $Texte;

        return $this;
    }

    public function getCommentateur(): ?User
    {
        return $this->Commentateur;
    }

    public function setCommentateur(?User $Commentateur): static
    {
        $this->Commentateur = $Commentateur;

        return $this;
    }

    public function getRencontre(): ?Rencontre
    {
        return $this->Rencontre;
    }

    public function setRencontre(?Rencontre $Rencontre): static
    {
        $this->Rencontre = $Rencontre;

        return $this;
    }
}
