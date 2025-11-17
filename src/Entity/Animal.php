<?php

namespace App\Entity;

use App\Repository\AnimalRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AnimalRepository::class)]
class Animal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $id_animal = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $type_animal = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $date_naissance = null;

    #[ORM\Column(length: 255)]
    private ?string $sexe = null;

    #[ORM\Column]
    private ?float $poids = null;

    #[ORM\Column(length: 255)]
    private ?string $couleur = null;

    #[ORM\Column]
    private ?\DateTime $date_enregistrement = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdAnimal(): ?int
    {
        return $this->id_animal;
    }

    public function setIdAnimal(int $id_animal): static
    {
        $this->id_animal = $id_animal;

        return $this;
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

    public function setDateNaissance(\DateTime $date_naissance): static
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
}
