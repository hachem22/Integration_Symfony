<?php

namespace App\Entity;

use App\Repository\EvenementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EvenementRepository::class)]
class Evenement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $Nom = null;

    #[ORM\Column(length: 255)]
    private ?string $Description = null;

    #[ORM\Column]
    private ?int $Capacite = null;

    #[ORM\ManyToOne(inversedBy: 'evenements')]
    private ?Utilisateur $utilisateurevent = null;

    #[ORM\ManyToOne(inversedBy: 'evenements')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Formation $formations = null;

    #[ORM\Column(length: 255)]
    private ?string $lieu = null;



    public function __construct()
    {
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

    public function getDescription(): ?string
    {
        return $this->Description;
    }

    public function setDescription(string $Description): static
    {
        $this->Description = $Description;

        return $this;
    }

    public function getCapacite(): ?int
    {
        return $this->Capacite;
    }

    public function setCapacite(int $Capacite): static
    {
        $this->Capacite = $Capacite;

        return $this;
    }

    public function getUtilisateurevent(): ?Utilisateur
    {
        return $this->utilisateurevent;
    }

    public function setUtilisateurevent(?Utilisateur $utilisateurevent): static
    {
        $this->utilisateurevent = $utilisateurevent;

        return $this;
    }

    public function getFormations(): ?Formation
    {
        return $this->formations;
    }

    public function setFormations(?Formation $formations): static
    {
        $this->formations = $formations;

        return $this;
    }

    public function getLieu(): ?string
    {
        return $this->lieu;
    }

    public function setLieu(string $lieu): static
    {
        $this->lieu = $lieu;

        return $this;
    }


}
