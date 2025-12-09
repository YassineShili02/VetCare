<?php

namespace App\Entity;

use App\Repository\CliniqueRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CliniqueRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Clinique
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom de la clinique est obligatoire")]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: "Le nom doit contenir au moins {{ limit }} caractères",
        maxMessage: "Le nom ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "L'adresse est obligatoire")]
    #[Assert\Length(
        max: 255,
        maxMessage: "L'adresse ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $adresse = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Assert\Length(
        max: 10,
        maxMessage: "Le code postal ne peut pas dépasser {{ limit }} caractères"
    )]
    #[Assert\Regex(
        pattern: "/^[0-9]{4,10}$/",
        message: "Le code postal doit contenir uniquement des chiffres"
    )]
    private ?string $codePostal = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Length(
        max: 100,
        maxMessage: "Le nom de la ville ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $ville = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: "Le numéro de téléphone est obligatoire")]
    #[Assert\Regex(
        pattern: "/^[0-9\s\+\-\(\)]+$/",
        message: "Le numéro de téléphone contient des caractères invalides"
    )]
    #[Assert\Length(
        max: 20,
        maxMessage: "Le téléphone ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $telephone = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Email(message: "L'adresse email '{{ value }}' n'est pas valide")]
    #[Assert\Length(
        max: 255,
        maxMessage: "L'email ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $email = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(
        max: 2000,
        maxMessage: "La description ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $description = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $horairesOuverture = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(message: "L'URL du site web n'est pas valide")]
    #[Assert\Length(
        max: 255,
        maxMessage: "L'URL ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $siteWeb = null;

    #[ORM\Column]
    private ?bool $actif = true;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logo = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $services = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    #[Assert\Positive(message: "Le tarif doit être positif")]
    private ?string $tarifConsultation = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Range(
        min: 0,
        max: 5,
        notInRangeMessage: "La note doit être entre {{ min }} et {{ max }}"
    )]
    private ?float $noteGlobale = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateModification = null;

    // --- RELATIONS ---

    // Relation BIDIRECTIONNELLE avec Rendezvous (votre entité)
    #[ORM\OneToMany(targetEntity: Rendezvous::class, mappedBy: 'clinique', cascade: ['persist'])]
    private Collection $rendezvous;

    public function __construct()
    {
        $this->rendezvous = new ArrayCollection();
        $this->dateCreation = new \DateTime();
        $this->actif = true;
        $this->services = [];
        $this->horairesOuverture = [
            'lundi' => ['ouvert' => true, 'debut' => '08:00', 'fin' => '18:00'],
            'mardi' => ['ouvert' => true, 'debut' => '08:00', 'fin' => '18:00'],
            'mercredi' => ['ouvert' => true, 'debut' => '08:00', 'fin' => '18:00'],
            'jeudi' => ['ouvert' => true, 'debut' => '08:00', 'fin' => '18:00'],
            'vendredi' => ['ouvert' => true, 'debut' => '08:00', 'fin' => '18:00'],
            'samedi' => ['ouvert' => true, 'debut' => '09:00', 'fin' => '13:00'],
            'dimanche' => ['ouvert' => false, 'debut' => null, 'fin' => null]
        ];
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

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;
        return $this;
    }

    public function getCodePostal(): ?string
    {
        return $this->codePostal;
    }

    public function setCodePostal(?string $codePostal): static
    {
        $this->codePostal = $codePostal;
        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(?string $ville): static
    {
        $this->ville = $ville;
        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getHorairesOuverture(): ?array
    {
        return $this->horairesOuverture;
    }

    public function setHorairesOuverture(?array $horairesOuverture): static
    {
        $this->horairesOuverture = $horairesOuverture;
        return $this;
    }

    public function getSiteWeb(): ?string
    {
        return $this->siteWeb;
    }

    public function setSiteWeb(?string $siteWeb): static
    {
        $this->siteWeb = $siteWeb;
        return $this;
    }

    public function isActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): static
    {
        $this->actif = $actif;
        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): static
    {
        $this->logo = $logo;
        return $this;
    }

    public function getServices(): ?array
    {
        return $this->services;
    }

    public function setServices(?array $services): static
    {
        $this->services = $services;
        return $this;
    }

    public function getTarifConsultation(): ?string
    {
        return $this->tarifConsultation;
    }

    public function setTarifConsultation(?string $tarifConsultation): static
    {
        $this->tarifConsultation = $tarifConsultation;
        return $this;
    }

    public function getNoteGlobale(): ?float
    {
        return $this->noteGlobale;
    }

    public function setNoteGlobale(?float $noteGlobale): static
    {
        $this->noteGlobale = $noteGlobale;
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

    /**
     * @return Collection<int, Rendezvous>
     */
    public function getRendezvous(): Collection
    {
        return $this->rendezvous;
    }

    public function addRendezvous(Rendezvous $rendezvous): static
    {
        if (!$this->rendezvous->contains($rendezvous)) {
            $this->rendezvous->add($rendezvous);
            $rendezvous->setClinique($this);
        }

        return $this;
    }

    public function removeRendezvous(Rendezvous $rendezvous): static
    {
        if ($this->rendezvous->removeElement($rendezvous)) {
            if ($rendezvous->getClinique() === $this) {
                $rendezvous->setClinique(null);
            }
        }

        return $this;
    }

    // --- MÉTHODES UTILITAIRES ---

    public function __toString(): string
    {
        return $this->nom ?? 'Clinique';
    }

    /**
     * Retourne l'adresse complète formatée
     */
    public function getAdresseComplete(): string
    {
        $parts = array_filter([
            $this->adresse,
            $this->codePostal,
            $this->ville
        ]);
        
        return implode(', ', $parts);
    }

    /**
     * Compte le nombre total de rendez-vous
     */
    public function getNombreRendezvous(): int
    {
        return $this->rendezvous->count();
    }

    /**
     * Filtre les rendez-vous par statut
     */
    public function getRendezvousParStatut(string $statut): Collection
    {
        return $this->rendezvous->filter(
            fn(Rendezvous $rdv) => $rdv->getStatut() === $statut
        );
    }

    /**
     * Retourne les rendez-vous du jour
     */
    public function getRendezvousAujourdhui(): Collection
    {
        $aujourd_hui = new \DateTime();
        
        return $this->rendezvous->filter(function(Rendezvous $rdv) use ($aujourd_hui) {
            return $rdv->getDateHeure()->format('Y-m-d') === $aujourd_hui->format('Y-m-d');
        });
    }

    /**
     * Vérifie si la clinique est ouverte aujourd'hui
     */
    public function isOuvertAujourdhui(): bool
    {
        $jourActuel = strtolower((new \DateTime())->format('l'));
        
        // Traduction des jours anglais vers français
        $jours = [
            'monday' => 'lundi',
            'tuesday' => 'mardi',
            'wednesday' => 'mercredi',
            'thursday' => 'jeudi',
            'friday' => 'vendredi',
            'saturday' => 'samedi',
            'sunday' => 'dimanche'
        ];
        
        $jourFrancais = $jours[$jourActuel] ?? null;
        
        if ($jourFrancais && isset($this->horairesOuverture[$jourFrancais])) {
            return $this->horairesOuverture[$jourFrancais]['ouvert'] ?? false;
        }
        
        return false;
    }

    /**
     * Retourne les horaires d'aujourd'hui
     */
    public function getHorairesAujourdhui(): ?array
    {
        $jourActuel = strtolower((new \DateTime())->format('l'));
        
        $jours = [
            'monday' => 'lundi',
            'tuesday' => 'mardi',
            'wednesday' => 'mercredi',
            'thursday' => 'jeudi',
            'friday' => 'vendredi',
            'saturday' => 'samedi',
            'sunday' => 'dimanche'
        ];
        
        $jourFrancais = $jours[$jourActuel] ?? null;
        
        if ($jourFrancais && isset($this->horairesOuverture[$jourFrancais])) {
            return $this->horairesOuverture[$jourFrancais];
        }
        
        return null;
    }

    /**
     * Ajoute un service à la clinique
     */
    public function addService(string $service): static
    {
        if (!in_array($service, $this->services ?? [])) {
            $services = $this->services ?? [];
            $services[] = $service;
            $this->services = $services;
        }
        
        return $this;
    }

    /**
     * Retire un service de la clinique
     */
    public function removeService(string $service): static
    {
        if ($this->services) {
            $key = array_search($service, $this->services);
            if ($key !== false) {
                unset($this->services[$key]);
                $this->services = array_values($this->services);
            }
        }
        
        return $this;
    }

    /**
     * Vérifie si la clinique offre un service
     */
    public function hasService(string $service): bool
    {
        return in_array($service, $this->services ?? []);
    }

    /**
     * Retourne la classe CSS pour le badge de statut
     */
    public function getStatutBadgeClass(): string
    {
        return $this->actif ? 'success' : 'danger';
    }

    /**
     * Retourne le label du statut
     */
    public function getStatutLabel(): string
    {
        return $this->actif ? 'Active' : 'Inactive';
    }

    /**
     * Retourne la note sous forme d'étoiles
     */
    public function getNoteEtoiles(): string
    {
        if (!$this->noteGlobale) {
            return 'Pas encore noté';
        }
        
        $etoiles = str_repeat('⭐', (int) round($this->noteGlobale));
        return $etoiles . ' (' . number_format($this->noteGlobale, 1) . '/5)';
    }

    /**
     * Trouve le prochain rendez-vous confirmé
     */
    public function getProchainRendezvous(): ?Rendezvous
    {
        $maintenant = new \DateTime();
        $prochains = $this->rendezvous->filter(
            fn(Rendezvous $rdv) => $rdv->getDateHeure() > $maintenant && 
                                    $rdv->getStatut() === 'confirme'
        );
        
        if ($prochains->isEmpty()) {
            return null;
        }
        
        $prochainArray = $prochains->toArray();
        usort($prochainArray, fn($a, $b) => $a->getDateHeure() <=> $b->getDateHeure());
        
        return $prochainArray[0] ?? null;
    }

    /**
     * Compte les rendez-vous confirmés
     */
    public function getNombreRendezvousConfirmes(): int
    {
        return $this->getRendezvousParStatut('confirme')->count();
    }

    /**
     * Compte les rendez-vous en attente
     */
    public function getNombreRendezvousEnAttente(): int
    {
        return $this->getRendezvousParStatut('en_attente')->count();
    }

    /**
     * Retourne le revenu total (rendez-vous payés)
     */
    public function getRevenuTotal(): float
    {
        $total = 0;
        
        foreach ($this->rendezvous as $rdv) {
            if ($rdv->getStatutPaiement() === 'paye' && $rdv->getMontantPaiement()) {
                $total += (float) $rdv->getMontantPaiement();
            }
        }
        
        return $total;
    }

    /**
     * Retourne les rendez-vous d'une période
     */
    public function getRendezvousPeriode(\DateTimeInterface $debut, \DateTimeInterface $fin): Collection
    {
        return $this->rendezvous->filter(function(Rendezvous $rdv) use ($debut, $fin) {
            $dateRdv = $rdv->getDateHeure();
            return $dateRdv >= $debut && $dateRdv <= $fin;
        });
    }

    /**
     * Vérifie si la clinique est ouverte à une heure donnée
     */
    public function isOuvertA(\DateTimeInterface $dateTime): bool
    {
        $jourActuel = strtolower($dateTime->format('l'));
        
        $jours = [
            'monday' => 'lundi',
            'tuesday' => 'mardi',
            'wednesday' => 'mercredi',
            'thursday' => 'jeudi',
            'friday' => 'vendredi',
            'saturday' => 'samedi',
            'sunday' => 'dimanche'
        ];
        
        $jourFrancais = $jours[$jourActuel] ?? null;
        
        if (!$jourFrancais || !isset($this->horairesOuverture[$jourFrancais])) {
            return false;
        }
        
        $horaires = $this->horairesOuverture[$jourFrancais];
        
        if (!$horaires['ouvert']) {
            return false;
        }
        
        $heureActuelle = $dateTime->format('H:i');
        
        return $heureActuelle >= $horaires['debut'] && $heureActuelle <= $horaires['fin'];
    }
}