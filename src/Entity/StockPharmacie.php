<?php

// src/Entity/StockPharmacie.php
namespace App\Entity;

use Symfony\Bridge\Doctrine\ArgumentResolver\EntityValueResolver;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use App\Repository\StockPharmacieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: StockPharmacieRepository::class)]
class StockPharmacie
{
    public const TYPE_MEDICAMENT = 'Médicament';
    public const TYPE_PARAMEDICAL = 'Paramédical';
    public const TYPE_COMPLEMENT = 'Complément alimentaire';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column]
    private ?float $prix = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $details = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(type: "text")]
    private ?string $description = null;

    #[ORM\Column]
    private ?int $quantite = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $statu = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateExpiration = null;

    #[ORM\OneToMany(targetEntity: Commande::class, mappedBy: 'medicament')]
    private Collection $commandes;

    #[ORM\Column(type: 'boolean')]
    private bool $actif = true;

    /**
     * @var Collection<int, Panier>
     */
    #[ORM\OneToMany(targetEntity: Panier::class, mappedBy: 'produit')]
    private Collection $paniers;

    public function __construct()
    {
        $this->commandes = new ArrayCollection();
        $this->paniers = new ArrayCollection();
    }

    public function getStatu(): ?string
    {
        return $this->statu;
    }

    public function setStatu(?string $statu = null): self
    {
        if ($statu !== null) {
            $this->statu = $statu;
        } else {
            // Logique automatique basée sur la quantité
            if ($this->quantite > 5) {
                $this->statu = 'En stock';
            } elseif ($this->quantite > 0) {
                $this->statu = 'Stock faible';
            } else {
                $this->statu = 'Rupture';
            }
        }
        return $this;
    }

    // Méthode utilitaire pour mettre à jour le statut automatiquement
    public function updateStatu(): self
    {
        return $this->setStatu();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        if (!in_array($type, [self::TYPE_MEDICAMENT, self::TYPE_PARAMEDICAL, self::TYPE_COMPLEMENT])) {
            throw new \InvalidArgumentException("Type invalide");
        }
        
        $this->type = $type;
        return $this;
    }

    public static function getTypeChoices(): array
    {
        return [
            'Médicament' => self::TYPE_MEDICAMENT,
            'Paramédical' => self::TYPE_PARAMEDICAL,
            'Complément alimentaire' => self::TYPE_COMPLEMENT,
        ];
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): self
    {
        $this->prix = $prix;
        return $this;
    }

    public function getDetails(): ?string
    {
        return $this->details;
    }

    public function setDetails(?string $details): self
    {
        $this->details = $details;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): self
    {
        $this->quantite = $quantite;
        return $this;
    }

    public function incrementQuantite(): self
    {
        $this->quantite++;
        return $this;
    }

    public function getDateExpiration(): ?\DateTimeInterface
    {
        return $this->dateExpiration;
    }

    public function setDateExpiration(?\DateTimeInterface $dateExpiration): self
    {
        $this->dateExpiration = $dateExpiration;
        return $this;
    }

    public function isActif(): bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): self
    {
        $this->actif = $actif;
        return $this;
    }

    /**
     * @return Collection<int, Panier>
     */
    public function getPaniers(): Collection
    {
        return $this->paniers;
    }

    public function addPanier(Panier $panier): static
    {
        if (!$this->paniers->contains($panier)) {
            $this->paniers->add($panier);
            $panier->setProduit($this);
        }

        return $this;
    }

    public function removePanier(Panier $panier): static
    {
        if ($this->paniers->removeElement($panier)) {
            // set the owning side to null (unless already changed)
            if ($panier->getProduit() === $this) {
                $panier->setProduit(null);
            }
        }

        return $this;
    }
}
