<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
#[ORM\HasLifecycleCallbacks]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column(type: Types::JSON)]
    private array $roles = [];

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(length: 100)]
    private ?string $firstName = null;

    #[ORM\Column(length: 100)]
    private ?string $lastName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $address = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $phone = null;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Animal::class)]
    private Collection $animals;

    #[ORM\Column(name: 'profile_photo', length: 255, nullable: true)]
    private ?string $profilePhoto = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->animals = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTimeImmutable();
        }
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * Méthode ajoutée : Récupère le rôle principal de l'utilisateur
     */
    public function getMainRole(): string
    {
        $roles = $this->getRoles();
        
        // Priorité des rôles (Admin > Vétérinaire > User)
        if (in_array('ROLE_ADMIN', $roles)) {
            return 'ROLE_ADMIN';
        }
        if (in_array('ROLE_VET', $roles)) {
            return 'ROLE_VET';
        }
        return 'ROLE_USER';
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * Méthode ajoutée : Récupère le nom complet
     */
    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;
        return $this;
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

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;
        return $this;
    }

    public function getProfilePhoto(): ?string
    {
        return $this->profilePhoto;
    }

    public function setProfilePhoto(?string $profilePhoto): static
    {
        $this->profilePhoto = $profilePhoto;
        return $this;
    }

    /**
     * @return Collection<int, Animal>
     */
    public function getAnimals(): Collection
    {
        return $this->animals;
    }

    public function addAnimal(Animal $animal): static
    {
        if (!$this->animals->contains($animal)) {
            $this->animals->add($animal);
            $animal->setOwner($this);
        }

        return $this;
    }

    public function removeAnimal(Animal $animal): static
    {
        if ($this->animals->removeElement($animal)) {
            if ($animal->getOwner() === $this) {
                $animal->setOwner(null);
            }
        }

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function eraseCredentials(): void
    {
        // Si tu stockes des données temporaires sensibles, efface-les ici
    }

    /**
     * Méthode ajoutée : Renvoie le nom du rôle sous forme lisible
     */
    public function getRoleLabel(): string
    {
        $mainRole = $this->getMainRole();
        
        switch ($mainRole) {
            case 'ROLE_ADMIN':
                return 'Administrateur';
            case 'ROLE_VET':
                return 'Vétérinaire';
            case 'ROLE_USER':
            default:
                return 'Utilisateur';
        }
    }

    /**
     * Méthode ajoutée : Vérifie si l'utilisateur a un rôle spécifique
     */
    public function hasRole(string $role): bool
    {
        return in_array($role, $this->getRoles());
    }

    /**
     * Méthode ajoutée : Vérifie si l'utilisateur est admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('ROLE_ADMIN');
    }

    /**
     * Méthode ajoutée : Vérifie si l'utilisateur est vétérinaire
     */
    public function isVeterinarian(): bool
    {
        return $this->hasRole('ROLE_VET');
    }

    /**
     * Méthode ajoutée : Vérifie si l'utilisateur est un utilisateur standard
     */
    public function isStandardUser(): bool
    {
        return $this->hasRole('ROLE_USER') && 
               !$this->hasRole('ROLE_ADMIN') && 
               !$this->hasRole('ROLE_VET');
    }

    /**
     * Méthode ajoutée : Renvoie les initiales de l'utilisateur
     */
    public function getInitials(): string
    {
        $firstNameInitial = $this->firstName ? strtoupper(substr($this->firstName, 0, 1)) : 'U';
        $lastNameInitial = $this->lastName ? strtoupper(substr($this->lastName, 0, 1)) : 'S';
        return $firstNameInitial . $lastNameInitial;
    }

    /**
     * Méthode ajoutée : Vérifie si l'utilisateur peut créer un certain type de compte
     */
    public function canCreateRole(string $role): bool
    {
        // Seuls les admins peuvent créer des comptes admin
        if ($role === 'ROLE_ADMIN') {
            return $this->hasRole('ROLE_ADMIN');
        }
        
        // Tout le monde peut créer des comptes user et vet
        return in_array($role, ['ROLE_USER', 'ROLE_VET']);
    }

    /**
     * Méthode ajoutée : Retourne une classe CSS basée sur le rôle
     */
    public function getRoleCssClass(): string
    {
        $mainRole = $this->getMainRole();
        
        switch ($mainRole) {
            case 'ROLE_ADMIN':
                return 'badge-admin';
            case 'ROLE_VET':
                return 'badge-vet';
            case 'ROLE_USER':
            default:
                return 'badge-user';
        }
    }

    /**
     * Méthode ajoutée : Retourne une icône basée sur le rôle
     */
    public function getRoleIcon(): string
    {
        $mainRole = $this->getMainRole();
        
        switch ($mainRole) {
            case 'ROLE_ADMIN':
                return '⚙️';
            case 'ROLE_VET':
                return '🐾';
            case 'ROLE_USER':
            default:
                return '👤';
        }
    }

    /**
     * Méthode ajoutée : Vérifie si l'utilisateur peut modifier un autre utilisateur
     */
    public function canEditUser(User $otherUser): bool
    {
        // Un utilisateur peut toujours se modifier lui-même
        if ($this->getId() === $otherUser->getId()) {
            return true;
        }
        
        // Les admins peuvent modifier tous les utilisateurs
        if ($this->isAdmin()) {
            return true;
        }
        
        // Les vétérinaires peuvent modifier les utilisateurs normaux
        if ($this->isVeterinarian() && $otherUser->isStandardUser()) {
            return true;
        }
        
        return false;
    }

    /**
     * Méthode ajoutée : Vérifie si l'utilisateur peut supprimer un autre utilisateur
     */
    public function canDeleteUser(User $otherUser): bool
    {
        // Un utilisateur ne peut pas se supprimer lui-même
        if ($this->getId() === $otherUser->getId()) {
            return false;
        }
        
        // Seuls les admins peuvent supprimer d'autres utilisateurs
        return $this->isAdmin();
    }

    /**
     * Méthode ajoutée : Retourne une couleur basée sur le rôle
     */
    public function getRoleColor(): string
    {
        $mainRole = $this->getMainRole();
        
        switch ($mainRole) {
            case 'ROLE_ADMIN':
                return '#e74c3c'; // Rouge
            case 'ROLE_VET':
                return '#2ecc71'; // Vert
            case 'ROLE_USER':
            default:
                return '#3498db'; // Bleu
        }
    }

    /**
     * Méthode ajoutée : Retourne une description du rôle
     */
    public function getRoleDescription(): string
    {
        $mainRole = $this->getMainRole();
        
        switch ($mainRole) {
            case 'ROLE_ADMIN':
                return 'Administrateur système - Accès complet';
            case 'ROLE_VET':
                return 'Vétérinaire - Gestion des consultations';
            case 'ROLE_USER':
            default:
                return 'Client - Propriétaire d\'animal';
        }
    }

    /**
     * Méthode ajoutée : Formate les rôles pour l'affichage
     */
    public function getFormattedRoles(): string
    {
        $roles = $this->getRoles();
        $formatted = [];
        
        foreach ($roles as $role) {
            $formatted[] = str_replace('ROLE_', '', $role);
        }
        
        return implode(', ', $formatted);
    }
}