<?php

// src/Entity/Utilisateur.php

namespace App\Entity;

use App\Enum\UtilisateurRole;
use App\Enum\MedecinSpecialite;
use App\Repository\UtilisateurRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Process\Process;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    #[ORM\OneToMany(targetEntity: DossierMedical::class, mappedBy: 'patient')]
    private Collection $dossiersMedicaux;

    public function __construct()
    {
        $this->dossiersMedicaux = new ArrayCollection();
        $this->rendezVousPris = new ArrayCollection();
        $this->rendezVousMedecin = new ArrayCollection();
        $this->paniers = new ArrayCollection();
    }
    #[ORM\OneToMany(targetEntity: RendezVous::class, mappedBy: 'patient')]
    private Collection $rendezVousPris;

    #[ORM\OneToMany(targetEntity: RendezVous::class, mappedBy: 'medecin')]
    private Collection $rendezVousMedecin;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom est obligatoire.")]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: "Le nom doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le nom ne doit pas dépasser {{ limit }} caractères."
    )]
    private ?string $Nom = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le prénom est obligatoire.")]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: "Le prénom doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le prénom ne doit pas dépasser {{ limit }} caractères."
    )]
    private ?string $Prenom = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "L'email est obligatoire.")]
    #[Assert\Email(message: "Veuillez entrer une adresse email valide.")]
    private ?string $Email = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "L'adresse est obligatoire.")]
    #[Assert\Length(
        min: 5,
        max: 255,
        minMessage: "L'adresse doit contenir au moins {{ limit }} caractères.",
        maxMessage: "L'adresse ne doit pas dépasser {{ limit }} caractères."
    )]
    private ?string $Adress = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Le numéro de téléphone est obligatoire.")]
    #[Assert\Regex(
        pattern: "/^\d{8}$/",
        message: "Le numéro de téléphone doit contenir exactement 8 chiffres."
    )]
    private ?int $Tel = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(
        max: 100,
        maxMessage: "Le grade ne doit pas dépasser {{ limit }} caractères."
    )]
    private ?string $Grade = null;

    #[ORM\Column(enumType: UtilisateurRole::class)]
    #[Assert\NotBlank(message: "Le rôle utilisateur est obligatoire.")]
    private ?UtilisateurRole $utilisateurRole = null;

    #[ORM\Column(enumType: MedecinSpecialite::class, nullable: true)]
    private ?MedecinSpecialite $medecinSpecilaite = null;

    #[ORM\ManyToOne(inversedBy: 'ListeUtilisateur')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Service $service = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $password = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $resetToken = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $resetTokenExpiresAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;
/**
     * @var Collection<int, Panier>
     */
    #[ORM\OneToMany(targetEntity: Panier::class, mappedBy: 'utilisateur')]
    private Collection $paniers;

  
    public function getRendezVousPris(): Collection
    {
        return $this->rendezVousPris;
    }
    public function getRendezVousMedecin(): Collection
    {
        return $this->rendezVousMedecin;
    }
    public function addRendezVousPris(RendezVous $rendezVous): static
    {
        if (!$this->rendezVousPris->contains($rendezVous)) {
            $this->rendezVousPris->add($rendezVous);
            $rendezVous->setPatient($this);
        }
        return $this;
    }
    public function addRendezVousMedecin(RendezVous $rendezVous): static
    {
        if (!$this->rendezVousMedecin->contains($rendezVous)) {
            $this->rendezVousMedecin->add($rendezVous);
            $rendezVous->setMedecin($this);
        }
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;
        return $this;
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->Nom;
    }

    public function setNom(string $Nom): static
    {
        $this->Nom = $Nom;
        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->Prenom;
    }

    public function setPrenom(string $Prenom): static
    {
        $this->Prenom = $Prenom;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->Email;
    }

    public function setEmail(string $Email): static
    {
        $this->Email = $Email;
        return $this;
    }

    public function getAdress(): ?string
    {
        return $this->Adress;
    }

    public function setAdress(string $Adress): static
    {
        $this->Adress = $Adress;
        return $this;
    }

    public function getTel(): ?int
    {
        return $this->Tel;
    }

    public function setTel(int $Tel): static
    {
        $this->Tel = $Tel;
        return $this;
    }

    public function getGrade(): ?string
    {
        return $this->Grade;
    }

    public function setGrade(string $Grade): static
    {
        $this->Grade = $Grade;
        return $this;
    }

    public function getUtilisateurRole(): ?UtilisateurRole
    {
        return $this->utilisateurRole;
    }

    public function setUtilisateurRole(UtilisateurRole $utilisateurRole): static
    {
        $this->utilisateurRole = $utilisateurRole;
        return $this;
    }

    public function getMedecinSpecilaite(): ?MedecinSpecialite
    {
        return $this->medecinSpecilaite;
    }

    public function setMedecinSpecilaite(?MedecinSpecialite $medecinSpecilaite): static
    {
        $this->medecinSpecilaite = $medecinSpecilaite;
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

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    public function setResetToken(?string $resetToken): static
    {
        $this->resetToken = $resetToken;
        return $this;
    }

    public function getResetTokenExpiresAt(): ?\DateTimeInterface
    {
        return $this->resetTokenExpiresAt;
    }

    public function setResetTokenExpiresAt(?\DateTimeInterface $resetTokenExpiresAt): static
    {
        $this->resetTokenExpiresAt = $resetTokenExpiresAt;
        return $this;
    }

    /**
     * Returns the identifier used for authentication (e.g., email).
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->Email;
    }

    /**
     * Returns the roles granted to the user.
     */
    public function getRoles(): array
    {
        // Map the enum value to a Symfony role
        return ['ROLE_' . strtoupper($this->utilisateurRole->value)];
    }

    /**
     * Removes sensitive data from the user.
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
    }
    public function getDossiersMedicaux(): Collection
    {
        return $this->dossiersMedicaux;
    }

    public function addDossierMedical(DossierMedical $dossierMedical): static
    {
        if (!$this->dossiersMedicaux->contains($dossierMedical)) {
            $this->dossiersMedicaux->add($dossierMedical);
            $dossierMedical->setPatient($this);
        }
        return $this;
    }

    public function removeDossierMedical(DossierMedical $dossierMedical): static
    {
        if ($this->dossiersMedicaux->removeElement($dossierMedical)) {
            if ($dossierMedical->getPatient() === $this) {
                $dossierMedical->setPatient(null);
            }
        }
        return $this;
    }
    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $failedLoginAttempts = 0;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $lockedUntil = null;


    // Getters and Setters
    public function getFailedLoginAttempts(): int
    {
        return $this->failedLoginAttempts;
    }

    public function setFailedLoginAttempts(int $failedLoginAttempts): self
    {
        $this->failedLoginAttempts = $failedLoginAttempts;
        return $this;
    }

    public function getLockedUntil(): ?\DateTimeInterface
    {
        return $this->lockedUntil;
    }

    public function setLockedUntil(?\DateTimeInterface $lockedUntil): self
    {
        $this->lockedUntil = $lockedUntil;
        return $this;
    }
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $faceEncoding = null;

    // Getters and Setters
    public function getFaceEncoding(): ?array
    {
        return $this->faceEncoding;
    }

    public function setFaceEncoding(?array $faceEncoding): self
    {
        $this->faceEncoding = $faceEncoding;
        return $this;
    }

    /**
     * Computes and sets the face encoding for the user.
     */
    public function computeAndSetFaceEncoding(string $projectDir): void
    {
        if ($this->getImage()) {
            $imagePath = $projectDir . '/public/uploads/' . $this->getImage();

            // Call the Python script to compute face encoding
            $pythonScriptPath = $projectDir . '/scripts/face_encoding.py';
            $process = new Process(['python3', $pythonScriptPath, $imagePath]);
            $process->run();

            if ($process->isSuccessful()) {
                $result = json_decode($process->getOutput(), true);
                if (isset($result['face_encoding'])) {
                    $this->setFaceEncoding($result['face_encoding']);
                }
            }
        }
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
            $panier->setUtilisateur($this);
        }

        return $this;
    }

    public function removePanier(Panier $panier): static
    {
        if ($this->paniers->removeElement($panier)) {
            // set the owning side to null (unless already changed)
            if ($panier->getUtilisateur() === $this) {
                $panier->setUtilisateur(null);
            }
        }

        return $this;
    }
}
