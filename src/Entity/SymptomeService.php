<?php

namespace App\Entity;

use App\Repository\SymptomeServiceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SymptomeServiceRepository::class)]
class SymptomeService
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $symptome = null;

    #[ORM\Column(length: 255)]
    private ?string $service = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSymptome(): ?string
    {
        return $this->symptome;
    }

    public function setSymptome(string $symptome): static
    {
        $this->symptome = $symptome;

        return $this;
    }

    public function getService(): ?string
    {
        return $this->service;
    }

    public function setService(string $service): static
    {
        $this->service = $service;

        return $this;
    }
}
