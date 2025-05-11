<?php

namespace App\Entity;

use App\Repository\RendezVousRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: RendezVousRepository::class)]
class RendezVous
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\GreaterThan(
        "tomorrow",
        message: "La date de rendez-vous doit être supérieure à la date actuelle plus un jour."
    )]

    private ?\DateTimeInterface $date = null;

    #[ORM\ManyToOne(targetEntity: Planning::class, inversedBy: 'rendezVous')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Planning $planning = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'rendezVous')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Utilisateur $medecin = null;
    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'rendezVous')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Utilisateur $patient = null;


    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $rendezVousStatus = null;

    #[ORM\ManyToOne(targetEntity: Service::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Service $service = null;

    #[ORM\Column(type: 'string', length: 5, nullable: true)]
    private ?string $heure = null;

    public function getHeure(): ?string
    {
        return $this->heure;
    }

    public function setHeure(?string $heure): self
    {
        $this->heure = $heure;
        return $this;
    }

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

    public function getPlanning(): ?Planning
    {
        return $this->planning;
    }

    public function setPlanning(?Planning $planning): self
    {
        $this->planning = $planning;
        return $this;
    }

    public function getMedecin(): ?Utilisateur
    {
        return $this->medecin;
    }

    public function setMedecin(?Utilisateur $medecin): static
    {
        $this->medecin = $medecin;
        return $this;
    }
    public function getPatient(): ?Utilisateur
    {
        return $this->patient;
    }

    public function setPatient(?Utilisateur $patient): static
    {
        $this->patient = $patient;
        return $this;
    }

    public function getRendezVousStatus(): ?string
    {
        return $this->rendezVousStatus;
    }

    public function setRendezVousStatus(?string $rendezVousStatus): static
    {
        $this->rendezVousStatus = $rendezVousStatus;
        return $this;
    }

    public function getService(): ?Service
    {
        return $this->service;
    }

    public function setService(?Service $service): static
    {
        $this->service = $service;
        return $this;
    }

    
}