<?php

namespace App\Entity;

use App\Enum\ReclamationStatut;
use App\Repository\TraitementReclamationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TraitementReclamationRepository::class)]
class TraitementReclamation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le commentaire ne peut pas être vide.")]
    #[Assert\Length(
        min: 10,
        minMessage: "Le commentaire doit contenir au moins {{ limit }} caractères."
    )]
    private ?string $commentaire = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank(message: "La date de traitement est requise.")]
    #[Assert\GreaterThanOrEqual("now", message: "La date de traitement ne peut pas être dans le passé.")]
    private ?\DateTimeInterface $datetraitement = null;

    #[ORM\Column(enumType: ReclamationStatut::class)]
    #[Assert\NotNull(message: "L'état de la réclamation est requis.")]
    private ?ReclamationStatut $etat = null;

    #[ORM\ManyToOne(targetEntity: Reclamation::class, inversedBy: 'traitements')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Reclamation $reclamation = null;
    #[ORM\Column(type: 'float', nullable: true)]
private ?float $rating = null;
#[ORM\Column(type: 'integer', options: ['default' => 0])]
private int $ratingCount = 0;
public function getRatingCount(): int
{
    return $this->ratingCount;
}

public function setRatingCount(int $ratingCount): self
{
    $this->ratingCount = $ratingCount;

    return $this;
}

public function getRating(): ?float
{
    return $this->rating;
}

public function setRating(float $rating): self
{
    $this->rating = $rating;
    return $this;
}
    

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(string $commentaire): static
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    public function getDatetraitement(): ?\DateTimeInterface
    {
        return $this->datetraitement;
    }

    public function setDatetraitement(\DateTimeInterface $datetraitement): static
    {
        $this->datetraitement = $datetraitement;

        return $this;
    }

    public function getEtat(): ?ReclamationStatut
    {
        return $this->etat;
    }

    public function setEtat(ReclamationStatut|string $etat): self
    {
        if (is_string($etat)) {
            $etat = ReclamationStatut::tryFrom($etat); // Convertir la chaîne en Enum
        }
        
        $this->etat = $etat;
        return $this;
    }

    public function getReclamation(): ?Reclamation
    {
        return $this->reclamation;
    }

    public function setReclamation(?Reclamation $reclamation): self
    {
        $this->reclamation = $reclamation;
        return $this;
    }
    public function addRating(float $rating): self
    {
        $this->ratingCount++;
        $this->rating = (($this->rating * ($this->ratingCount - 1)) + $rating) / $this->ratingCount;
        return $this;
    }
}
