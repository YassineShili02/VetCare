<?php
namespace App\Entity;

use App\Repository\TraitementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TraitementRepository::class)]
class Traitement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 1000, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToMany(targetEntity: Medicament::class)]
    private Collection $medicaments;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $statut = 'pending';

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateCreation = null;

    public function __construct()
    {
        $this->medicaments = new ArrayCollection();
        $this->dateCreation = new \DateTime();
    }

    // Getters et Setters...
    public function getId(): ?int { return $this->id; }
    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = $nom; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }
    public function getStatut(): ?string { return $this->statut; }
    public function setStatut(string $statut): static { $this->statut = $statut; return $this; }
    public function getDateCreation(): ?\DateTimeInterface { return $this->dateCreation; }
    public function setDateCreation(\DateTimeInterface $dateCreation): static { $this->dateCreation = $dateCreation; return $this; }

    public function getMedicaments(): Collection { return $this->medicaments; }
    public function addMedicament(Medicament $medicament): static {
        if (!$this->medicaments->contains($medicament)) {
            $this->medicaments->add($medicament);
        }
        return $this;
    }
    public function removeMedicament(Medicament $medicament): static {
        $this->medicaments->removeElement($medicament);
        return $this;
    }
}