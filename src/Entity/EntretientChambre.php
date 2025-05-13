<?php

namespace App\Entity;

use App\Enum\StatutEntretientChambre;
use App\Enum\TypeEntretient;
use App\Repository\EntretientChambreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use App\Enum\UtilisateurRole;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: EntretientChambreRepository::class)]
class EntretientChambre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(enumType: TypeEntretient::class)]
    #[Assert\NotNull(message: "Le type d'entretien est obligatoire.")]
    private ?TypeEntretient $type = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull(message: "La date de début est obligatoire.")]
    #[Assert\Type(type: \DateTimeInterface::class, message: "La date de début doit être une date valide.")]
    #[Assert\GreaterThan("today", message: "La date de début doit être dans le futur.")]
    private ?\DateTimeInterface $datedebut = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull(message: "La date de fin est obligatoire.")]
    #[Assert\Type(type: \DateTimeInterface::class, message: "La date de fin doit être une date valide.")]
    #[Assert\GreaterThan(propertyPath: "datedebut", message: "La date de fin doit être après la date de début.")]
    private ?\DateTimeInterface $datefin = null;

    #[ORM\Column(enumType: StatutEntretientChambre::class)]
    #[Assert\NotNull(message: "Le statut est obligatoire.")]
    private ?StatutEntretientChambre $statut = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(
        max: 20,
        maxMessage: "Les détails ne peuvent pas dépasser 20 caractères."
    )]
    private ?string $details = null;

    #[ORM\ManyToOne(inversedBy: 'entretient')]
    #[Assert\NotNull(message: "Une chambre doit être associée à l'entretien.")]
    private ?Chambre $chambre = null;

    #[ORM\ManyToOne(targetEntity:"Utilisateur")]
    #[ORM\JoinColumn(nullable: true)]
    private ?Utilisateur $femmedemenage = null;

   

   

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?TypeEntretient
    {
        return $this->type;
    }

    public function setType(TypeEntretient $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getDatedebut(): ?\DateTimeInterface
    {
        return $this->datedebut;
    }

    public function setDatedebut(\DateTimeInterface $datedebut): static
    {
        $this->datedebut = $datedebut;

        return $this;
    }

    public function getDatefin(): ?\DateTimeInterface
    {
        return $this->datefin;
    }

    public function setDatefin(\DateTimeInterface $datefin): static
    {
        $this->datefin = $datefin;

        return $this;
    }

    public function getStatut(): ?StatutEntretientChambre
    {
        return $this->statut;
    }

    public function setStatut(StatutEntretientChambre $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getDetails(): ?string
    {
        return $this->details;
    }

    public function setDetails(?string $details): static
    {
        $this->details = $details;

        return $this;
    }

    public function getChambre(): ?Chambre
    {
        return $this->chambre;
    }

    public function setChambre(?Chambre $chambre): static
    {
        $this->chambre = $chambre;

        return $this;
    }

    /**
     * 🚀 Validation personnalisée : Vérifier si la chambre est disponible avant d'ajouter un entretien
     */
    #[Assert\Callback]
    public function validateChambreDisponibilite(ExecutionContextInterface $context): void
    {
        if ($this->chambre && $this->chambre->getActive() !== 'Disponible') {
            $context->buildViolation("L'entretien ne peut être créé que pour une chambre d'etat  disponible.")
                ->atPath("chambre")
                ->addViolation();
        }
    }

    /**
     * 🏥 Validation personnalisée : Vérifier que la durée de l’entretien ne dépasse pas 30 jours
     */
    #[Assert\Callback]
    public function validateDuree(ExecutionContextInterface $context): void
    {
        if ($this->datedebut && $this->datefin) {
            $interval = $this->datedebut->diff($this->datefin);
            if ($interval->days > 30) {
                $context->buildViolation("La durée de l'entretien ne peut pas dépasser 30 jours.")
                    ->atPath("datefin")
                    ->addViolation();
            }
        }
    }

    public function getFemmedemenage(): ?Utilisateur
    {
        return $this->femmedemenage;
    }

    public function setFemmedemenage(?Utilisateur $femmedemenage): static
    {
        $this->femmedemenage = $femmedemenage;

        return $this;
    }
    public function autoCompleteDescription(): ?string
{
    if ($this->type === null) {
        return null;
    }

    switch ($this->type) {
        case TypeEntretient::NETTOYAGE:
            return "Nettoyage complet de la chambre";
       
        case TypeEntretient::REPARATION:
            return "Vérification et réparation des équipements";
       
        case TypeEntretient::AUTRE:
            return "Travaux de rénovation et mise à jour";
       
       
       
        default:
            return null;
    }
}

   
}

