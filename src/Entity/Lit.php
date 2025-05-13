<?php

namespace App\Entity;

use App\Enum\LitStatut;
use App\Repository\LitRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Enum\UtilisateurRole;
use PhpParser\Node\Expr\Cast\String_;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LitRepository::class)]
class Lit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255,unique: true)]
    #[Assert\NotBlank(message: "Le numéro du lit ne peut pas être vide.")]
    #[Assert\Length(
        max: 5,
        maxMessage: "Le numéro du lit ne peut pas dépasser {{ limit }} caractères."
    )]
    #[Assert\Regex(
        pattern: "/^[A-Z0-9\-]+$/",
        message: "Le numéro du lit ne peut contenir que des lettres majuscules, des chiffres et des tirets."
    )]
    private ?string $num = null;

    #[ORM\Column(type: 'string')]
    #[Assert\NotBlank(message: "Le type du lit ne peut pas être vide.")]
   
    private string $type  ;
     #[ORM\Column(type: 'string')]
    #[Assert\NotBlank(message: "Le statut du lit ne peut pas être vide.")]
   
    private string $status  ;

    #[ORM\ManyToOne(inversedBy: 'lits')]
    private ?Chambre $chambre = null;

    #[ORM\ManyToOne(targetEntity:"Utilisateur")]
    #[ORM\JoinColumn(nullable: true)]
    private ?Utilisateur $patient = null;

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

    public function setType(string $type): self
    {
        $this->type = $type;
       
   
       
        return $this;
    }
    public function getStatus(): ?string
    {
        return $this->status;
    }
    public function setStatus(string $status): self
    {
        $this->status = $status;
       
    if ($this->chambre) {
            $this->chambre->updateStatus();
        }
       
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

    public function getPatient(): ?Utilisateur
    {
        return $this->patient;
    }

    public function setPatient(?Utilisateur $patient): static
    {
        $this->patient = $patient;
   
        // Mettre à jour le statut du lit
        $this->type = $patient ? 'occupée' : 'libre';
   
        // Mettre à jour la chambre si elle existe
        if ($this->chambre) {
            $this->chambre->updateStatus();
        }
   
        return $this;
    }
   
}
