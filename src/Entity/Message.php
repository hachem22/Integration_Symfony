<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Blog::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Blog $blog = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $author = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $senderName = null;

    #[ORM\Column(type: 'text')]
    private ?string $content = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateEnvoi = null;

    #[ORM\Column(type: 'string', length: 255, options: ['default' => 'unread'])]
    private ?string $status = 'unread';

    // Constructor to set the default dateEnvoi
    public function __construct()
    {
        $this->dateEnvoi = new \DateTime();
    }

    // Getters and Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBlog(): ?Blog
    {
        return $this->blog;
    }

    public function setBlog(?Blog $blog): self
    {
        $this->blog = $blog;
        return $this;
    }

    public function getAuthor(): ?Utilisateur
    {
        return $this->author;
    }

    public function setAuthor(?Utilisateur $author): self
    {
        $this->author = $author;
        return $this;
    }

    public function getSenderName(): ?string
    {
        return $this->senderName;
    }

    public function setSenderName(string $senderName): self
    {
        $this->senderName = $senderName;
        return $this;
    }

    public function getReceiverEmail(): ?string
    {
        return $this->receiverEmail;
    }

    public function setReceiverEmail(?string $receiverEmail): self
    {
        $this->receiverEmail = $receiverEmail;
        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getDateEnvoi(): ?\DateTimeInterface
    {
        return $this->dateEnvoi;
    }

    public function setDateEnvoi(\DateTimeInterface $dateEnvoi): self
    {
        $this->dateEnvoi = $dateEnvoi;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }
}