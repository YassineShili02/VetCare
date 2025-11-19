<?php

namespace App\Entity;

use App\Repository\RendezvousRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RendezvousRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Rendezvous
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null; // Contrôle, urgence, vaccination...

    #[ORM\Column]
    private ?int $duree = null; // durée en minutes

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $commentaireClient = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notesVeterinaire = null;

    #[ORM\Column]
    private ?int $confirmation = null;

    #[ORM\Column(length: 50)]
    private ?string $modePaiementPrevu = null; // cash, carte...

    // ========== GETTERS & SETTERS ==========

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
{
    $this->updatedAt = $updatedAt;
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

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(int $duree): static
    {
        $this->duree = $duree;
        return $this;
    }

    public function getCommentaireClient(): ?string
    {
        return $this->commentaireClient;
    }

    public function setCommentaireClient(?string $commentaireClient): static
    {
        $this->commentaireClient = $commentaireClient;
        return $this;
    }

    public function getNotesVeterinaire(): ?string
    {
        return $this->notesVeterinaire;
    }

    public function setNotesVeterinaire(?string $notesVeterinaire): static
    {
        $this->notesVeterinaire = $notesVeterinaire;
        return $this;
    }

    public function isConfirmation(): ?bool
{
    return $this->confirmation;
}

public function setConfirmation(?bool $confirmation): self
{
    $this->confirmation = $confirmation;
    return $this;
}


    public function getModePaiementPrevu(): ?string
    {
        return $this->modePaiementPrevu;
    }

    public function setModePaiementPrevu(string $modePaiementPrevu): static
    {
        $this->modePaiementPrevu = $modePaiementPrevu;
        return $this;
    }

    // ========== AUTO timestamps ==========

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();

    // Valeur par défaut pour confirmation
    if ($this->confirmation === null) {
        $this->confirmation = false;
    }
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
