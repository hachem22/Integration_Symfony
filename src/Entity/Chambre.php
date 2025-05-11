<?php

namespace App\Entity;

use App\Repository\ChambreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: ChambreRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Chambre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank(message: "Le numéro de chambre est obligatoire.")]
    #[Assert\Regex(
        pattern: "/^[A-Z0-9\-]+$/",
        message: "Le numéro du chambre ne peut contenir que des lettres majuscules, des chiffres et des tirets."
    )]
    #[Assert\Length(
        min: 3, max: 5,
        minMessage: "Le numéro doit comporter au moins 3 caractères.",
        maxMessage: "Le numéro ne peut pas dépasser 5 caractères."
    )]
    private ?string $num = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le type de chambre est obligatoire.")]
    #[Assert\Choice(choices: ['simple', 'double', 'suite'], message: "Le type de chambre doit être 'simple', 'double' ou 'suite'.")]
    private ?string $type = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La localisation est obligatoire.")]
    #[Assert\Length(
        max: 100,
        maxMessage: "La localisation ne peut pas dépasser 100 caractères."
    )]
    private ?string $localisation = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "La capacité est obligatoire.")]
    #[Assert\Positive(message: "La capacité doit être un nombre positif.")]
    #[Assert\Range(
        min: 1, max: 10,
        notInRangeMessage: "La capacité doit être comprise entre 1 et 10."
    )]
    private ?int $capacite = null;

    #[ORM\Column(type: 'string', length: 50)]
    #[Assert\NotBlank(message: "Le statut de la chambre est obligatoire.")]
    #[Assert\Choice(choices: ['disponible', 'occupee', 'maintenance'], message: "La valeur doit être 'disponible', 'occupee' ou 'maintenance'.")]
    private ?string $active = null;

    #[ORM\OneToMany(targetEntity: EntretientChambre::class, mappedBy: 'chambre', cascade: ['remove'])]
    private Collection $entretient;

    #[ORM\OneToMany(targetEntity: Lit::class, mappedBy: 'chambre', cascade: ['remove'])]
    private Collection $lits;

    #[ORM\ManyToOne(inversedBy: 'chambres')]
    #[Assert\NotNull(message: "La chambre doit être rattachée à un service.")]
    private ?Service $position = null;

    public function __construct()
    {
        $this->entretient = new ArrayCollection();
        $this->lits = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNum(): ?string
    {
        return $this->num;
    }

    public function setNum(string $num): static
    {
        $this->num = $num;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getLocalisation(): ?string
    {
        return $this->localisation;
    }

    public function setLocalisation(string $localisation): static
    {
        $this->localisation = $localisation;
        return $this;
    }

    public function getCapacite(): ?int
    {
        return $this->capacite;
    }

    public function setCapacite(int $capacite): static
    {
        $this->capacite = $capacite;
        return $this;
    }

    public function getActive(): ?string
    {
        return $this->active;
    }

    public function setActive(?string $active): self
    {
        $this->active = $active;
        return $this;
    }

    /**
     * @return Collection<int, EntretientChambre>
     */
    public function getEntretient(): Collection
    {
        return $this->entretient;
    }

    public function addEntretient(EntretientChambre $entretient): static
    {
        if (!$this->entretient->contains($entretient)) {
            $this->entretient->add($entretient);
            $entretient->setChambre($this);
        }
        return $this;
    }

    public function removeEntretient(EntretientChambre $entretient): static
    {
        if ($this->entretient->removeElement($entretient)) {
            if ($entretient->getChambre() === $this) {
                $entretient->setChambre(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Lit>
     */
    public function getLits(): Collection
    {
        return $this->lits;
    }

    public function addLit(Lit $lit): static
    {
        if (!$this->lits->contains($lit)) {
            $this->lits->add($lit);
            $lit->setChambre($this);
        }
        $this->updateStatus();
        return $this;
    }

    public function removeLit(Lit $lit): static
    {
        if ($this->lits->removeElement($lit)) {
            if ($lit->getChambre() === $this) {
                $lit->setChambre(null);
            }
        }
        $this->updateStatus();
        return $this;
    }

    public function getPosition(): ?Service
    {
        return $this->position;
    }

    public function setPosition(?Service $position): static
    {
        $this->position = $position;
        return $this;
    }

    #[ORM\PreUpdate]
    #[ORM\PrePersist]
    public function generateNumChambre(): void
    {
        if ($this->position && $this->num) {
            $prefix = strtoupper(substr($this->position->getNom(), 0, 3));

            if (!str_starts_with($this->num, $prefix)) {
                $this->num = $prefix . $this->num;
            }
        }
    }

    public function updateStatus(): void
    {
        $tousOccupes = true;

        foreach ($this->lits as $lit) {
            if ($lit->getType() !== 'occupe') {
                $tousOccupes = false;
                break;
            }
        }

        $this->active = $tousOccupes ? 'occupee' : 'disponible';
    }

    /**
     * 🚀 Validation personnalisée : Vérifier que la capacité de la chambre correspond au nombre de lits
     */
    #[Assert\Callback]
    public function validateCapaciteLit(ExecutionContextInterface $context): void
    {
        if ($this->capacite && count($this->lits) > $this->capacite) {
            $context->buildViolation("Le nombre de lits ne peut pas dépasser la capacité de la chambre.")
                ->atPath("capacite")
                ->addViolation();
        }
    }
}
