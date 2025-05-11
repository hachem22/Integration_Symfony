<?php

namespace App\Entity;

use App\Repository\ServiceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ServiceRepository::class)]
class Service
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $Nom = null;

    /**
     * @var Collection<int, Utilisateur>
     */
    #[ORM\OneToMany(targetEntity: Utilisateur::class, mappedBy: 'service')]
    private Collection $ListeUtilisateur;

    public function __construct()
    {
        $this->ListeUtilisateur = new ArrayCollection();
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
    

    /**
     * @return Collection<int, Utilisateur>
     */
    public function getListeUtilisateur(): Collection
    {
        return $this->ListeUtilisateur;
    }

    public function addListeUtilisateur(Utilisateur $listeUtilisateur): static
    {
        if (!$this->ListeUtilisateur->contains($listeUtilisateur)) {
            $this->ListeUtilisateur->add($listeUtilisateur);
            $listeUtilisateur->setService($this);
        }

        return $this;
    }

    public function removeListeUtilisateur(Utilisateur $listeUtilisateur): static
    {
        if ($this->ListeUtilisateur->removeElement($listeUtilisateur)) {
            // set the owning side to null (unless already changed)
            if ($listeUtilisateur->getService() === $this) {
                $listeUtilisateur->setService(null);
            }
        }

        return $this;
    }
}
