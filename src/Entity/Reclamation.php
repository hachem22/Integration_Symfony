<?php

namespace App\Entity;

use App\Enum\ReclamationStatut;
use App\Enum\ReclamationType;
use App\Repository\ReclamationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReclamationRepository::class)]
class Reclamation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La description ne peut pas être vide.")]
    #[Assert\Length(
        min: 10,
        max: 255,
        minMessage: "La description doit contenir au moins {{ limit }} caractères.",
        maxMessage: "La description ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $description = null;

    #[ORM\Column(enumType: ReclamationType::class)]
    #[Assert\NotNull(message: "Veuillez sélectionner un type de réclamation.")]
    private ?ReclamationType $type = null;

    #[ORM\Column(enumType: ReclamationStatut::class)]
    private ?ReclamationStatut $statut = ReclamationStatut::EN_ATTENTE;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull(message: "La date de création est requise.")]
    private ?\DateTimeInterface $datecreation = null;
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La cible ne peut pas être vide.")]
    #[Assert\Length(
    min: 5,
    max: 255,
    minMessage: "La cible doit contenir au moins {{ limit }} caractères.",
    maxMessage: "La cible ne peut pas dépasser {{ limit }} caractères."
)]

private ?string $cible = null;

    #[ORM\ManyToOne(targetEntity: Categorie::class, inversedBy: 'reclamations')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Veuillez choisir une catégorie.")]
    private ?Categorie $categorie = null;
    

    /**
     * @var Collection<int, TraitementReclamation>
     */
   
    
    #[ORM\OneToMany(mappedBy: 'reclamation', targetEntity: TraitementReclamation::class, cascade: ['persist', 'remove'])]
    private Collection $traitementReclamations;
    

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    public function __construct()
    {
        $this->traitementReclamations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getType(): ?ReclamationType
    {
        return $this->type;
    }

    public function setType(ReclamationType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getStatut(): ReclamationStatut
    {
        return $this->statut;
    }

    public function setStatut(ReclamationStatut|string $statut): self
    {
        if (is_string($statut)) {
            $statut = ReclamationStatut::tryFrom($statut);
        }
        
        $this->statut = $statut;
        return $this;
    }
    public function getCible(): ?string
{
    return $this->cible;
}

public function setCible(string $cible): static
{
    $this->cible = $cible;

    return $this;
}
    


    public function getDatecreation(): ?\DateTimeInterface
    {
        return $this->datecreation;
    }

    public function setDatecreation(\DateTimeInterface $datecreation): static
    {
        $this->datecreation = $datecreation;

        return $this;
    }

    public function getCategorie(): ?Categorie
    {
        return $this->categorie;
    }

    public function setCategorie(?Categorie $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }

    /**
     * @return Collection<int, TraitementReclamation>
     */
    public function getTraitementReclamations(): Collection
    {
        return $this->traitementReclamations;
    }

    public function addTraitementReclamation(TraitementReclamation $traitementReclamation): static
    {
        if (!$this->traitementReclamations->contains($traitementReclamation)) {
            $this->traitementReclamations->add($traitementReclamation);
            $traitementReclamation->setTraitements($this);
        }

        return $this;
    }

    public function removeTraitementReclamation(TraitementReclamation $traitementReclamation): static
    {
        if ($this->traitementReclamations->removeElement($traitementReclamation)) {
            // set the owning side to null (unless already changed)
            if ($traitementReclamation->getTraitements() === $this) {
                $traitementReclamation->setTraitements(null);
            }
        }

        return $this;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): self
    {
        $this->utilisateur = $utilisateur;
        return $this;
    }
   
}
