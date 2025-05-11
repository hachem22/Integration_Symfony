<?php

namespace App\Entity;

use App\Repository\DossierMedicalRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DossierMedicalRepository::class)]
class DossierMedical
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le groupe sanguin est obligatoire.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "Le groupe sanguin ne peut pas dépasser {{ limit }} caractères."
    )]
    #[Assert\Choice(
        choices: ["A+", "A-", "B+", "B-", "AB+", "AB-", "O+", "O-"],
        message: "Le groupe sanguin doit être valide (ex: A+, B-, AB+, O-)."
    )]
    private ?string $groupeSanguin = null; // Corrected property name to camelCase

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: "Les allergies sont obligatoires.")]
    #[Assert\Length(
        min: 5,
        max: 1000,
        maxMessage: "Les allergies ne peuvent pas dépasser {{ limit }} caractères."
    )]
    #[Assert\Regex(
        pattern: "/^[a-zA-Z0-9\s\-,\.]+$/",
        message: "Les allergies ne peuvent contenir que des lettres, des chiffres, des espaces, des virgules, des tirets et des points."
    )]
    private ?string $allergies = null; // Corrected property name to camelCase

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: "Les vaccinations sont obligatoires.")]
    #[Assert\Length(
        min: 4,
        max: 1000,
        maxMessage: "Les vaccinations ne peuvent pas dépasser {{ limit }} caractères."
    )]
    #[Assert\Regex(
        pattern: "/^[a-zA-Z0-9\s\-,\.]+$/",
        message: "Les vaccinations ne peuvent contenir que des lettres, des chiffres, des espaces, des virgules, des tirets et des points."
    )]
    private ?string $vaccinations = null; // Corrected property name to camelCase

    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\Length(
        min: 5,
        max: 1000,
        maxMessage: "Les antécédents médicaux ne peuvent pas dépasser {{ limit }} caractères."
    )]
    #[Assert\Regex(
        pattern: "/^[a-zA-Z0-9\s\-,\.]+$/",
        message: "Les antécédents médicaux ne peuvent contenir que des lettres, des chiffres, des espaces, des virgules, des tirets et des points."
    )]
    private ?string $antecedentsMedicaux = null; // Corrected property name to camelCase

    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\Length(
        min: 5,
        max: 1000,
        maxMessage: "Les traitements en cours ne peuvent pas dépasser {{ limit }} caractères."
    )]
    #[Assert\Regex(
        pattern: "/^[a-zA-Z0-9\s\-,\.]+$/",
        message: "Les traitements en cours ne peuvent contenir que des lettres, des chiffres, des espaces, des virgules, des tirets et des points."
    )]
    private ?string $traitementsEnCours = null; // Corrected property name to camelCase

    #[ORM\Column(type: 'text', nullable: true)]
   #[Assert\Length(
        min: 20,
        max: 1000,
        maxMessage: "Les remarques ne peuvent pas dépasser {{ limit }} caractères."
    )]
    #[Assert\Regex(
        pattern: "/^[a-zA-Z0-9\s\-,\.]+$/",
        message: "Les remarques ne peuvent contenir que des lettres, des chiffres, des espaces, des virgules, des tirets et des points."
    )]
    private ?string $remarques = null; // Corrected property name to camelCase

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'dossiersMedicaux')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Le patient est obligatoire.")]
    #[Assert\Valid]
    private ?Utilisateur $patient = null;

    /**
     * @var Collection<int, Visite>
     */
    #[ORM\OneToMany(targetEntity: Visite::class, mappedBy: 'dossierMedical')]
    private Collection $visites;

    public function __construct()
    {
        $this->visites = new ArrayCollection();
    }

    

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGroupeSanguin(): ?string
    {
        return $this->groupeSanguin;
    }

    public function setGroupeSanguin(string $groupeSanguin): static
    {
        $this->groupeSanguin = $groupeSanguin;
        return $this;
    }

    public function getAllergies(): ?string
    {
        return $this->allergies;
    }

    public function setAllergies(string $allergies): static
    {
        $this->allergies = $allergies;
        return $this;
    }

    public function getVaccinations(): ?string
    {
        return $this->vaccinations;
    }

    public function setVaccinations(string $vaccinations): static
    {
        $this->vaccinations = $vaccinations;
        return $this;
    }

    public function getAntecedentsMedicaux(): ?string
    {
        return $this->antecedentsMedicaux;
    }

    public function setAntecedentsMedicaux(?string $antecedentsMedicaux): static
    {
        $this->antecedentsMedicaux = $antecedentsMedicaux;
        return $this;
    }

    public function getTraitementsEnCours(): ?string
    {
        return $this->traitementsEnCours;
    }

    public function setTraitementsEnCours(?string $traitementsEnCours): static
    {
        $this->traitementsEnCours = $traitementsEnCours;
        return $this;
    }

    public function getRemarques(): ?string
    {
        return $this->remarques;
    }

    public function setRemarques(?string $remarques): static
    {
        $this->remarques = $remarques;
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

    /**
     * @return Collection<int, Visite>
     */
    public function getVisites(): Collection
    {
        return $this->visites;
    }

    public function addVisite(Visite $visite): static
    {
        if (!$this->visites->contains($visite)) {
            $this->visites->add($visite);
            $visite->setDossierMedical($this);
        }

        return $this;
    }

    public function removeVisite(Visite $visite): static
    {
        if ($this->visites->removeElement($visite)) {
            // set the owning side to null (unless already changed)
            if ($visite->getDossierMedical() === $this) {
                $visite->setDossierMedical(null);
            }
        }

        return $this;
    }

   
}