<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Veterinaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $nom = null;

    #[ORM\Column(length: 100)]
    private ?string $prenom = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $specialite = null;

    #[ORM\Column]
    private ?bool $actif = true;

    #[ORM\OneToMany(targetEntity: Rendezvous::class, mappedBy: 'veterinaire')]
    private Collection $rendezvous;

    #[ORM\OneToMany(targetEntity: DisponibiliteVeterinaire::class, mappedBy: 'veterinaire', cascade: ['persist', 'remove'])]
    private Collection $disponibilites;

    public function __construct()
    {
        $this->rendezvous = new ArrayCollection();
        $this->disponibilites = new ArrayCollection();
    }

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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): static
    {
        $this->telephone = $telephone;
        return $this;
    }

    public function getSpecialite(): ?string
    {
        return $this->specialite;
    }

    public function setSpecialite(?string $specialite): static
    {
        $this->specialite = $specialite;
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
            $rendezvous->setVeterinaire($this);
        }
        return $this;
    }

    public function removeRendezvous(Rendezvous $rendezvous): static
    {
        if ($this->rendezvous->removeElement($rendezvous)) {
            if ($rendezvous->getVeterinaire() === $this) {
                $rendezvous->setVeterinaire(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, DisponibiliteVeterinaire>
     */
    public function getDisponibilites(): Collection
    {
        return $this->disponibilites;
    }

    public function addDisponibilite(DisponibiliteVeterinaire $disponibilite): static
    {
        if (!$this->disponibilites->contains($disponibilite)) {
            $this->disponibilites->add($disponibilite);
            $disponibilite->setVeterinaire($this);
        }
        return $this;
    }

    public function removeDisponibilite(DisponibiliteVeterinaire $disponibilite): static
    {
        if ($this->disponibilites->removeElement($disponibilite)) {
            if ($disponibilite->getVeterinaire() === $this) {
                $disponibilite->setVeterinaire(null);
            }
        }
        return $this;
    }

    public function __toString(): string
    {
        return "Dr. {$this->prenom} {$this->nom}";
    }
}