<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\CommandeRepository;
use App\Entity\StockPharmacie;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
class Commande
{
    public const STATUT_EN_ATTENTE = 'En attente';
    public const STATUT_LIVREE = 'Livrée';
    public const STATUT_ANNULEE = 'Annulée';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'commandes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?StockPharmacie $stockPharmacie = null;

    #[ORM\Column]
    private ?int $quantite = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateCommande = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateLivraison = null;

    #[ORM\Column(length: 255)]
    private ?string $statut = self::STATUT_EN_ATTENTE;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $commentaire = null;

    public function __construct()
    {
        $this->dateCommande = new \DateTimeImmutable();
        // Par défaut, date de livraison = date de commande + 3 jours
        $this->dateLivraison = $this->dateCommande->modify('+3 days');
    }

    // Getters & Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStockPharmacie(): ?StockPharmacie
    {
        return $this->stockPharmacie;
    }

    public function setStockPharmacie(?StockPharmacie $stockPharmacie): self
    {
        $this->stockPharmacie = $stockPharmacie;
        return $this;
    }

    public function getDateCommande(): ?\DateTimeInterface
    {
        return $this->dateCommande;
    }

    public function setDateCommande(\DateTimeInterface $dateCommande): self
    {
        $this->dateCommande = $dateCommande;
        return $this;
    }

    public function getDateLivraison(): ?\DateTimeImmutable
    {
        return $this->dateLivraison;
    }

    public function setDateLivraison(\DateTimeImmutable $dateLivraison): self
    {
        $this->dateLivraison = $dateLivraison;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): self
    {
        $this->statut = $statut;
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

    public function isLivree(): bool
    {
        return $this->statut === self::STATUT_LIVREE;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): self
    {
        $this->commentaire = $commentaire;
        return $this;
    }
}
