<?php

namespace App\Entity;

use App\Repository\PlanningRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PlanningRepository::class)]
class Planning
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull(message: "La date disponible ne peut pas être vide.")]
    #[Assert\GreaterThan(
        "tomorrow",
        message: "La date  disponible doit être supérieure à la date actuelle plus un jour."
    )]
    private ?\DateTimeInterface $dateDisponible = null;

    #[ORM\Column(type: 'json')]
    // Assertions for NotNull and GreaterThan removed as they are not suitable for an array of dates.
    // This array stores dates (as strings 'Y-m-d') when the doctor is generally unavailable.
    private array $datesNonDisponibles = [];

    #[ORM\Column(type: 'json', nullable: true)]
    private array $tempsReserver = [];

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'plannings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $medecin = null;

    public function __construct()
    {
        $this->tempsReserver = [];
    }

    public function getId(): ?int
    {
        return $this->id;
    }
public function getDateDisponible(): ?\DateTimeInterface
    {
        return $this->dateDisponible;
    }

    public function setDateDisponible(\DateTimeInterface $dateDisponible): self
    {
        $this->dateDisponible = $dateDisponible;
        return $this;
    }
    public function getDatesNonDisponibles(): array
    {
        return $this->datesNonDisponibles;
    }

    public function setDatesNonDisponibles(array $datesNonDisponibles): self
    {
        $this->datesNonDisponibles = $datesNonDisponibles;
        return $this;
    }

    public function ajouterDateNonDisponible(string $date): self
    {
        if (!in_array($date, $this->datesNonDisponibles)) {
            $this->datesNonDisponibles[] = $date;
        }
        return $this;
    }

    public function supprimerDateNonDisponible(string $date): self
    {
        if (($key = array_search($date, $this->datesNonDisponibles)) !== false) {
            unset($this->datesNonDisponibles[$key]);
            $this->datesNonDisponibles = array_values($this->datesNonDisponibles);
        }
        return $this;
    }


    public function getTempsReserver(): array
    {
        return $this->tempsReserver;
    }

    public function setTempsReserver(array $tempsReserver): self
    {
        $this->tempsReserver = $tempsReserver;
        return $this;
    }

    public function ajouterTempsReserver(string $temps): self
    {
        if (!in_array($temps, $this->tempsReserver)) {
            $this->tempsReserver[] = $temps;
        }
        return $this;
    }

    public function supprimerTempsReserver(string $temps): self
    {
        if (($key = array_search($temps, $this->tempsReserver)) !== false) {
            unset($this->tempsReserver[$key]);
            $this->tempsReserver = array_values($this->tempsReserver);
        }
        return $this;
    }

    public function estTempsReserver(string $temps): bool
    {
        return in_array($temps, $this->tempsReserver);
    }

    public function getMedecin(): ?Utilisateur
    {
        return $this->medecin;
    }

    public function setMedecin(?Utilisateur $medecin): self
    {
        $this->medecin = $medecin;
        return $this;
    }
}