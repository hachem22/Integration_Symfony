<?php

namespace App\Entity;

use App\Repository\VisiteRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: VisiteRepository::class)]
class Visite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: "La date de visite est obligatoire.")]
    #[Assert\GreaterThan(
        "tomorrow",
        message: "La date de visite doit être supérieure à la date actuelle plus un jour."
    )]
    private ?\DateTimeInterface $dateVisite = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le diagnostic est obligatoire.")]
    #[Assert\Length(min: 10, max: 2000, minMessage: "Le diagnostic doit contenir au moins {{ limit }} caractères.")]
    private ?string $diagnostic = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "L'ordonnance est obligatoire.")]
    #[Assert\Length(min: 10, max: 2000, minMessage: "L'ordonnance doit contenir au moins {{ limit }} caractères.")]
    private ?string $ordonnance = null; // Corrected typo: 'ordonnace' -> 'ordonnance'

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le certificat est obligatoire.")]
    #[Assert\Length(min: 10, max: 255, minMessage: "Le certificat doit contenir au moins {{ limit }} caractères.")]
    private ?string $certificat = null; // Corrected typo: 'ceteficat' -> 'certificat'

    #[ORM\OneToOne(inversedBy: 'visite', cascade: ['persist', 'remove'])]
    private ?RendezVous $rdv = null;

    #[ORM\ManyToOne(inversedBy: 'visites')]
    #[Assert\NotNull(message: "Le dossier médical est obligatoire.")]

    private ?DossierMedical $dossierMedical = null; // Corrected property name to lowercase

    

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateVisite(): ?\DateTimeInterface
    {
        return $this->dateVisite;
    }

    public function setDateVisite(\DateTimeInterface $dateVisite): static
    {
        $this->dateVisite = $dateVisite;
        return $this;
    }

    public function getDiagnostic(): ?string
    {
        return $this->diagnostic;
    }

    public function setDiagnostic(string $diagnostic): static
    {
        $this->diagnostic = $diagnostic;
        return $this;
    }

    public function getOrdonnance(): ?string
    {
        return $this->ordonnance;
    }

    public function setOrdonnance(string $ordonnance): static
    {
        $this->ordonnance = $ordonnance;
        return $this;
    }

    public function getCertificat(): ?string
    {
        return $this->certificat;
    }

    public function setCertificat(string $certificat): static
    {
        $this->certificat = $certificat;
        return $this;
    }

    public function getRdv(): ?RendezVous
    {
        return $this->rdv;
    }

    public function setRdv(?RendezVous $rdv): static
    {
        $this->rdv = $rdv;
        return $this;
    }

    public function getDossierMedical(): ?DossierMedical
    {
        return $this->dossierMedical;
    }

    public function setDossierMedical(?DossierMedical $dossierMedical): static
    {
        $this->dossierMedical = $dossierMedical;

        return $this;
    }

   
}