<?php

namespace App\Entity;

use App\Repository\RendezvousRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RendezvousRepository::class)]
class Rendezvous
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateHeure = null;

    #[ORM\Column(length: 100)]
    private ?string $type = null;

    #[ORM\ManyToOne(inversedBy: 'rendezvous')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Veterinaire $veterinaire = null;

    #[ORM\Column(length: 50)]
    private ?string $statut = 'en_attente';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notesClient = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notesVeterinaire = null;

    #[ORM\Column(length: 50)]
    private ?string $statutPaiement = 'non_paye';

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $montantPaiement = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $methodePaiement = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $datePaiement = null;

    #[ORM\Column(length: 255)]
    private ?string $nomClient = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $emailClient = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $telephoneClient = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nomAnimal = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $especeAnimal = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateHeure(): ?\DateTimeInterface
    {
        return $this->dateHeure;
    }

    public function setDateHeure(\DateTimeInterface $dateHeure): static
    {
        $this->dateHeure = $dateHeure;
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

    public function getVeterinaire(): ?Veterinaire
    {
        return $this->veterinaire;
    }

    public function setVeterinaire(?Veterinaire $veterinaire): static
    {
        $this->veterinaire = $veterinaire;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    public function getNotesClient(): ?string
    {
        return $this->notesClient;
    }

    public function setNotesClient(?string $notesClient): static
    {
        $this->notesClient = $notesClient;
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

    public function getStatutPaiement(): ?string
    {
        return $this->statutPaiement;
    }

    public function setStatutPaiement(string $statutPaiement): static
    {
        $this->statutPaiement = $statutPaiement;
        return $this;
    }

    public function getMontantPaiement(): ?string
    {
        return $this->montantPaiement;
    }

    public function setMontantPaiement(?string $montantPaiement): static
    {
        $this->montantPaiement = $montantPaiement;
        return $this;
    }

    public function getMethodePaiement(): ?string
    {
        return $this->methodePaiement;
    }

    public function setMethodePaiement(?string $methodePaiement): static
    {
        $this->methodePaiement = $methodePaiement;
        return $this;
    }

    public function getDatePaiement(): ?\DateTimeInterface
    {
        return $this->datePaiement;
    }

    public function setDatePaiement(?\DateTimeInterface $datePaiement): static
    {
        $this->datePaiement = $datePaiement;
        return $this;
    }

    public function getNomClient(): ?string
    {
        return $this->nomClient;
    }

    public function setNomClient(string $nomClient): static
    {
        $this->nomClient = $nomClient;
        return $this;
    }

    public function getEmailClient(): ?string
    {
        return $this->emailClient;
    }

    public function setEmailClient(?string $emailClient): static
    {
        $this->emailClient = $emailClient;
        return $this;
    }

    public function getTelephoneClient(): ?string
    {
        return $this->telephoneClient;
    }

    public function setTelephoneClient(?string $telephoneClient): static
    {
        $this->telephoneClient = $telephoneClient;
        return $this;
    }

    public function getNomAnimal(): ?string
    {
        return $this->nomAnimal;
    }

    public function setNomAnimal(?string $nomAnimal): static
    {
        $this->nomAnimal = $nomAnimal;
        return $this;
    }

    public function getEspeceAnimal(): ?string
    {
        return $this->especeAnimal;
    }

    public function setEspeceAnimal(?string $especeAnimal): static
    {
        $this->especeAnimal = $especeAnimal;
        return $this;
    }

    public function getStatutBadgeClass(): string
    {
        return match($this->statut) {
            'en_attente' => 'warning',
            'confirme' => 'success',
            'refuse' => 'danger',
            'termine' => 'info',
            'annule' => 'secondary',
            default => 'secondary'
        };
    }

    public function getStatutPaiementBadgeClass(): string
    {
        return match($this->statutPaiement) {
            'non_paye' => 'danger',
            'paye' => 'success',
            'partiel' => 'warning',
            'rembourse' => 'info',
            default => 'secondary'
        };
    }
}
