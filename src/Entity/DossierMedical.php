<?php

namespace App\Entity;

use App\Repository\DossierMedicalRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: DossierMedicalRepository::class)]
class DossierMedical
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "id_dossier")]
    private ?int $id_dossier = null;

    #[ORM\Column(length: 255)]
    private ?string $numero_dossier = null;

    #[ORM\Column(type: "datetime")]
    private ?\DateTime $date_creation = null;

    #[ORM\Column(nullable: true)]
    private ?float $poids = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Etat est obligatoire")]
    private ?string $etat = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $images = [];

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(message: "Les notes sont obligatoires")]
    private ?string $notes_Veterinaire = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $allergies = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $vaccinations = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $antecedents_medicaux = null;

    // --- RELATION ONE TO ONE ---
    #[ORM\OneToOne(inversedBy: 'dossier_animal', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: "animal_id", referencedColumnName: "id_animal", nullable: true)]
    private ?Animal $animal = null;

    // --- GETTERS & SETTERS ---
    public function getIdDossier(): ?int
    {
        return $this->id_dossier;
    }

    public function getNumeroDossier(): ?string
    {
        return $this->numero_dossier;
    }

    public function setNumeroDossier(string $numero_dossier): static
    {
        $this->numero_dossier = $numero_dossier;
        return $this;
    }

    public function getDateCreation(): ?\DateTime
    {
        return $this->date_creation;
    }

    public function setDateCreation(\DateTime $date_creation): static
    {
        $this->date_creation = $date_creation;
        return $this;
    }

    public function getPoids(): ?float
    {
        return $this->poids;
    }

    public function setPoids(?float $poids): static
    {
        $this->poids = $poids;
        return $this;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): static
    {
        $this->etat = $etat;
        return $this;
    }

    public function getImages(): ?array
    {
        return $this->images;
    }

    public function setImages(?array $images): static
    {
        $this->images = $images;
        return $this;
    }

    public function getNotesVeterinaire(): ?string
    {
        return $this->notes_Veterinaire;
    }

    public function setNotesVeterinaire(?string $notes_Veterinaire): static
    {
        $this->notes_Veterinaire = $notes_Veterinaire;
        return $this;
    }

    public function getAllergies(): ?string
    {
        return $this->allergies;
    }

    public function setAllergies(?string $allergies): static
    {
        $this->allergies = $allergies;
        return $this;
    }

    public function getVaccinations(): ?string
    {
        return $this->vaccinations;
    }

    public function setVaccinations(?string $vaccinations): static
    {
        $this->vaccinations = $vaccinations;
        return $this;
    }

    public function getAntecedentsMedicaux(): ?string
    {
        return $this->antecedents_medicaux;
    }

    public function setAntecedentsMedicaux(?string $antecedents_medicaux): static
    {
        $this->antecedents_medicaux = $antecedents_medicaux;
        return $this;
    }

    public function getAnimal(): ?Animal
    {
        return $this->animal;
    }

    public function setAnimal(?Animal $animal): static
    {
        $this->animal = $animal;

        // Assure la liaison inverse
        if ($animal !== null && $animal->getDossierAnimal() !== $this) {
            $animal->setDossierAnimal($this);
        }

        return $this;
    }
}
