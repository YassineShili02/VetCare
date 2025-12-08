<?php

namespace App\Entity;

use App\Repository\RendezvousRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RendezvousRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Rendezvous
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank(message: "La date et l'heure sont obligatoires")]
    #[Assert\Type(\DateTimeInterface::class, message: "Format de date invalide")]
    #[Assert\GreaterThan("today", message: "La date du rendez-vous doit être dans le futur")]
    private ?\DateTimeInterface $dateHeure = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "Le type de rendez-vous est obligatoire")]
    #[Assert\Choice(
        choices: ['consultation', 'vaccination', 'chirurgie', 'urgence', 'controle', 'sterilisation', 'dentaire', 'autre'],
        message: "Veuillez sélectionner un type de consultation valide"
    )]
    #[Assert\Length(
        max: 100,
        maxMessage: "Le type ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $type = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Le statut est obligatoire")]
    #[Assert\Choice(
        choices: ['en_attente', 'confirme', 'refuse', 'termine', 'annule'],
        message: "Veuillez sélectionner un statut valide"
    )]
    private ?string $statut = 'en_attente';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(
        max: 1000,
        maxMessage: "Les notes ne peuvent pas dépasser {{ limit }} caractères"
    )]
    private ?string $notesClient = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(
        max: 2000,
        maxMessage: "Les notes ne peuvent pas dépasser {{ limit }} caractères"
    )]
    private ?string $notesVeterinaire = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Le statut de paiement est obligatoire")]
    #[Assert\Choice(
        choices: ['non_paye', 'paye', 'partiel', 'rembourse'],
        message: "Veuillez sélectionner un statut de paiement valide"
    )]
    private ?string $statutPaiement = 'non_paye';

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    #[Assert\Positive(message: "Le montant doit être positif")]
    #[Assert\Type(type: "numeric", message: "Le montant doit être un nombre")]
    private ?string $montantPaiement = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Choice(
        choices: ['especes', 'carte_bancaire', 'cheque', 'virement', 'mobile_payment'],
        message: "Veuillez sélectionner une méthode de paiement valide"
    )]
    private ?string $methodePaiement = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Assert\Type(\DateTimeInterface::class, message: "Format de date invalide")]
    private ?\DateTimeInterface $datePaiement = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom complet est obligatoire")]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: "Le nom doit contenir au moins {{ limit }} caractères",
        maxMessage: "Le nom ne peut pas dépasser {{ limit }} caractères"
    )]
    #[Assert\Regex(
        pattern: "/^[a-zA-ZÀ-ÿ\s\-']+$/u",
        message: "Le nom ne peut contenir que des lettres"
    )]
    private ?string $nomClient = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(message: "L'email est obligatoire", groups: ['registration'])]
    #[Assert\Email(message: "L'adresse email '{{ value }}' n'est pas valide")]
    #[Assert\Length(
        max: 255,
        maxMessage: "L'email ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $emailClient = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Assert\Regex(
        pattern: "/^[0-9\s\+\-\(\)]+$/",
        message: "Le numéro de téléphone contient des caractères invalides"
    )]
    #[Assert\Length(
        max: 20,
        maxMessage: "Le téléphone ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $telephoneClient = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(message: "Le nom de l'animal est obligatoire", groups: ['with_animal'])]
    #[Assert\Length(
        max: 255,
        maxMessage: "Le nom de l'animal ne peut pas dépasser {{ limit }} caractères"
    )]
    #[Assert\Regex(
        pattern: "/^[a-zA-ZÀ-ÿ\s\-']+$/u",
        message: "Le nom de l'animal ne peut contenir que des lettres"
    )]
    private ?string $nomAnimal = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Choice(
        choices: ['chien', 'chat', 'oiseau', 'rongeur', 'reptile', 'lapin', 'furet', 'autre'],
        message: "Veuillez sélectionner une espèce valide"
    )]
    private ?string $especeAnimal = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateModification = null;

    // --- RELATIONS (UNIDIRECTIONNELLES - pas besoin de modifier les autres entités) ---
    
    // Relation avec User - UNIDIRECTIONNELLE (pas d'inversedBy)
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'client_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?User $client = null;

    // Relation avec Animal - UNIDIRECTIONNELLE (pas d'inversedBy)
    #[ORM\ManyToOne(targetEntity: Animal::class)]
    #[ORM\JoinColumn(name: 'animal_id', referencedColumnName: 'id_animal', nullable: true, onDelete: 'SET NULL')]
    private ?Animal $animal = null;

    // Relation avec Clinique - BIDIRECTIONNELLE
    #[ORM\ManyToOne(targetEntity: Clinique::class, inversedBy: 'rendezvous')]
    #[ORM\JoinColumn(name: 'clinique_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Clinique $clinique = null;

    public function __construct()
    {
        $this->dateCreation = new \DateTime();
        $this->statut = 'en_attente';
        $this->statutPaiement = 'non_paye';
    }

    #[ORM\PreUpdate]
    public function setDateModificationValue(): void
    {
        $this->dateModification = new \DateTime();
    }

    // --- GETTERS & SETTERS ---

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

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): static
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    public function getDateModification(): ?\DateTimeInterface
    {
        return $this->dateModification;
    }

    public function setDateModification(?\DateTimeInterface $dateModification): static
    {
        $this->dateModification = $dateModification;
        return $this;
    }

    // --- RELATIONS GETTERS & SETTERS ---

    public function getClient(): ?User
    {
        return $this->client;
    }

    public function setClient(?User $client): static
    {
        $this->client = $client;
        return $this;
    }

    public function getAnimal(): ?Animal
    {
        return $this->animal;
    }

    public function setAnimal(?Animal $animal): static
    {
        $this->animal = $animal;
        return $this;
    }

    public function getClinique(): ?Clinique
    {
        return $this->clinique;
    }

    public function setClinique(?Clinique $clinique): static
    {
        $this->clinique = $clinique;
        return $this;
    }

    // --- MÉTHODES UTILITAIRES ---

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

    public function getTypeBadgeClass(): string
    {
        return match($this->type) {
            'urgence' => 'danger',
            'chirurgie' => 'warning',
            'vaccination' => 'info',
            'consultation' => 'primary',
            default => 'secondary'
        };
    }

    public function getStatutLabel(): string
    {
        return match($this->statut) {
            'en_attente' => 'En attente',
            'confirme' => 'Confirmé',
            'refuse' => 'Refusé',
            'termine' => 'Terminé',
            'annule' => 'Annulé',
            default => 'Inconnu'
        };
    }

    public function getTypeLabel(): string
    {
        return match($this->type) {
            'consultation' => 'Consultation',
            'vaccination' => 'Vaccination',
            'chirurgie' => 'Chirurgie',
            'urgence' => 'Urgence',
            'controle' => 'Contrôle',
            'sterilisation' => 'Stérilisation',
            'dentaire' => 'Soins dentaires',
            'autre' => 'Autre',
            default => 'Non défini'
        };
    }

    public function isPasse(): bool
    {
        return $this->dateHeure < new \DateTime();
    }

    public function isAVenir(): bool
    {
        return $this->dateHeure > new \DateTime();
    }

    public function isAujourdhui(): bool
    {
        $aujourd_hui = new \DateTime();
        return $this->dateHeure->format('Y-m-d') === $aujourd_hui->format('Y-m-d');
    }

    public function getDuree(): int
    {
        // Retourne la durée estimée en minutes selon le type
        return match($this->type) {
            'chirurgie' => 120,
            'urgence' => 60,
            'vaccination' => 30,
            'consultation' => 45,
            'controle' => 30,
            'sterilisation' => 90,
            'dentaire' => 60,
            default => 45
        };
    }

    public function __toString(): string
    {
        return sprintf(
            'RDV #%d - %s le %s',
            $this->id ?? 0,
            $this->nomClient ?? 'Client',
            $this->dateHeure ? $this->dateHeure->format('d/m/Y H:i') : 'Date inconnue'
        );
    }
}