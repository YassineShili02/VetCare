<?php

namespace App\Entity;

use App\Repository\AnimalRepository;
use Doctrine\DBAL\Types\FloatType;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AnimalRepository::class)]
class Animal
{
    public function __construct()
    {
        // Date enregistrée automatiquement à la création de l'objet
        $this->date_enregistrement = new \DateTime();
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "id_animal")]
    private ?int $id_animal = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Nom de l'animal est obligatoire")]
    #[Assert\Regex(pattern: "/^[a-zA-Z\s]+$/", message: "Le nom doit contenir uniquement des lettres")]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Type d'animal est obligatoire")]
    #[Assert\Regex(pattern: "/^[a-zA-Z\s]+$/", message: "Le type doit contenir uniquement des lettres")]
    private ?string $type_animal = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: false)]
    #[Assert\NotBlank(message: "La date de naissance est obligatoire")]
    #[Assert\LessThanOrEqual("today", message: "La date de naissance doit être dans le passé ou aujourd'hui")]
    private ?\DateTime $date_naissance = null;

    #[ORM\Column(length: 255)]
    #[Assert\Choice(choices: ['M', 'F'], message: "Le sexe doit être M ou F")]
    private ?string $sexe = null;

    #[ORM\Column(name: "poids" , type : "float")]
    #[Assert\NotBlank(message: "Le poids est obligatoire")]
    #[Assert\GreaterThanOrEqual(value: 1, message: "Le poids doit être strictement positif")]
    private ?float $poids = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La couleur doit etre obligatoire")]
    #[Assert\Regex(pattern: "/^[a-zA-Z\s]+$/", message: "La couleur doit contenir uniquement des lettres")]
    private ?string $couleur = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTime $date_enregistrement = null;

    #[ORM\OneToOne(mappedBy: 'animal', cascade: ['persist', 'remove'])]
    private ?DossierMedical $dossier_animal = null;


    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'animals')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;


    // --- ID ACCESSORS ---
    public function getIdAnimal(): ?int
    {
        return $this->id_animal;
    }

    // --- OTHER FIELDS ---
    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getTypeAnimal(): ?string
    {
        return $this->type_animal;
    }

    public function setTypeAnimal(string $type_animal): static
    {
        $this->type_animal = $type_animal;
        return $this;
    }

    public function getDateNaissance(): ?\DateTime
    {
        return $this->date_naissance;
    }

    public function setDateNaissance(?\DateTime $date_naissance): static
    {
        $this->date_naissance = $date_naissance;
        return $this;
    }

    public function getSexe(): ?string
    {
        return $this->sexe;
    }

    public function setSexe(string $sexe): static
    {
        $this->sexe = $sexe;
        return $this;
    }

    public function getPoids(): ?float
    {
        return $this->poids;
    }

    public function setPoids(float $poids): static
    {
        $this->poids = $poids;
        return $this;
    }

    public function getCouleur(): ?string
    {
        return $this->couleur;
    }

    public function setCouleur(string $couleur): static
    {
        $this->couleur = $couleur;
        return $this;
    }

    public function getDateEnregistrement(): ?\DateTime
    {
        return $this->date_enregistrement;
    }

    public function setDateEnregistrement(\DateTime $date_enregistrement): static
    {
        $this->date_enregistrement = $date_enregistrement;
        return $this;
    }

    // --- DOSSIER MEDICAL ONE TO ONE ---
    public function getDossierAnimal(): ?DossierMedical
    {
        return $this->dossier_animal;
    }

    public function setDossierAnimal(?DossierMedical $dossier_animal): static
    {
        $this->dossier_animal = $dossier_animal;

        // Assure la synchronisation inverse
        if ($dossier_animal !== null && $dossier_animal->getAnimal() !== $this) {
            $dossier_animal->setAnimal($this);
        }

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;
        return $this;
    }
}
