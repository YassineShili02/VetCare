<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\PasswordResetTokenRepository;

#[ORM\Entity(repositoryClass: PasswordResetTokenRepository::class)]
#[ORM\Table(name: 'password_reset_tokens')]
#[ORM\HasLifecycleCallbacks]
class PasswordResetToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(name: 'verification_code', length: 100)]
    private ?string $verificationCode = null;

    #[ORM\Column(name: 'created_at')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(name: 'expires_at')]
    private ?\DateTimeImmutable $expiresAt = null;

    #[ORM\Column(name: 'is_used', options: ['default' => false])]
    private bool $isUsed = false;

    #[ORM\Column(name: 'ip_address', length: 45, nullable: true)]
    private ?string $ipAddress = null;

    #[ORM\Column(name: 'user_agent', length: 255, nullable: true)]
    private ?string $userAgent = null;

    #[ORM\Column(name: 'used_at', nullable: true)]
    private ?\DateTimeImmutable $usedAt = null;

    #[ORM\Column(name: 'attempts', options: ['default' => 0])]
    private int $attempts = 0;

    #[ORM\Column(name: 'token_type', length: 20, options: ['default' => 'verification'])]
    private string $tokenType = 'verification';

    #[ORM\Column(name: 'metadata', type: 'json', nullable: true)]
    private ?array $metadata = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->expiresAt = (new \DateTimeImmutable())->modify('+15 minutes');
        $this->isUsed = false;
        $this->attempts = 0;
        $this->tokenType = 'verification';
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTimeImmutable();
        }
        
        if ($this->expiresAt === null) {
            $this->expiresAt = (new \DateTimeImmutable())->modify('+15 minutes');
        }
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        // Cette méthode peut être utilisée pour des timestamps d'update si nécessaire
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getVerificationCode(): ?string
    {
        return $this->verificationCode;
    }

    public function setVerificationCode(string $verificationCode): self
    {
        $this->verificationCode = $verificationCode;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(\DateTimeImmutable $expiresAt): self
    {
        $this->expiresAt = $expiresAt;
        return $this;
    }

    public function isExpired(): bool
    {
        return $this->expiresAt < new \DateTimeImmutable();
    }

    public function getIsUsed(): bool
    {
        return $this->isUsed;
    }

    public function isUsed(): bool
    {
        return $this->isUsed;
    }

    public function setIsUsed(bool $isUsed): self
    {
        $this->isUsed = $isUsed;
        
        if ($isUsed && $this->usedAt === null) {
            $this->usedAt = new \DateTimeImmutable();
        }
        
        return $this;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(?string $ipAddress): self
    {
        $this->ipAddress = $ipAddress;
        return $this;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(?string $userAgent): self
    {
        $this->userAgent = $userAgent;
        return $this;
    }

    public function getUsedAt(): ?\DateTimeImmutable
    {
        return $this->usedAt;
    }

    public function setUsedAt(?\DateTimeImmutable $usedAt): self
    {
        $this->usedAt = $usedAt;
        return $this;
    }

    public function getAttempts(): int
    {
        return $this->attempts;
    }

    public function setAttempts(int $attempts): self
    {
        $this->attempts = $attempts;
        return $this;
    }

    public function incrementAttempts(): self
    {
        $this->attempts++;
        return $this;
    }

    public function getTokenType(): string
    {
        return $this->tokenType;
    }

    public function setTokenType(string $tokenType): self
    {
        $this->tokenType = $tokenType;
        return $this;
    }

    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    public function setMetadata(?array $metadata): self
    {
        $this->metadata = $metadata;
        return $this;
    }

    /**
     * Vérifie si le token est valide (non utilisé et non expiré)
     */
    public function isValid(): bool
    {
        return !$this->isUsed && !$this->isExpired();
    }

    /**
     * Marque le token comme utilisé
     */
    public function markAsUsed(): self
    {
        $this->isUsed = true;
        $this->usedAt = new \DateTimeImmutable();
        return $this;
    }

    /**
     * Renouvelle le token avec une nouvelle date d'expiration
     */
    public function renew(int $minutes = 15): self
    {
        $this->expiresAt = (new \DateTimeImmutable())->modify("+{$minutes} minutes");
        return $this;
    }

    /**
     * Vérifie si le token a dépassé le nombre maximal de tentatives
     */
    public function hasExceededMaxAttempts(int $maxAttempts = 5): bool
    {
        return $this->attempts >= $maxAttempts;
    }

    /**
     * Retourne le temps restant avant expiration en minutes
     */
    public function getRemainingMinutes(): int
    {
        $now = new \DateTimeImmutable();
        $interval = $this->expiresAt->diff($now);
        
        return (int) $interval->format('%i') + ($interval->format('%h') * 60);
    }

    /**
     * Retourne le temps écoulé depuis la création en minutes
     */
    public function getElapsedMinutes(): int
    {
        $now = new \DateTimeImmutable();
        $interval = $now->diff($this->createdAt);
        
        return (int) $interval->format('%i') + ($interval->format('%h') * 60);
    }

    /**
     * Vérifie si le token a été utilisé récemment
     */
    public function wasUsedRecently(int $minutes = 5): bool
    {
        if (!$this->usedAt) {
            return false;
        }
        
        $limit = (new \DateTimeImmutable())->modify("-{$minutes} minutes");
        return $this->usedAt > $limit;
    }

    /**
     * Vérifie si le code correspond
     */
    public function matchesCode(string $code): bool
    {
        return $this->verificationCode === $code;
    }

    /**
     * Retourne une représentation simplifiée pour le logging
     */
    public function __toString(): string
    {
        return sprintf(
            'Token#%d (User: %d, Type: %s, Expires: %s)',
            $this->id,
            $this->user ? $this->user->getId() : 0,
            $this->tokenType,
            $this->expiresAt ? $this->expiresAt->format('Y-m-d H:i:s') : 'N/A'
        );
    }

    /**
     * Récupère les informations de sécurité
     */
    public function getSecurityInfo(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user ? $this->user->getId() : null,
            'created' => $this->createdAt ? $this->createdAt->format('Y-m-d H:i:s') : null,
            'expires' => $this->expiresAt ? $this->expiresAt->format('Y-m-d H:i:s') : null,
            'is_used' => $this->isUsed,
            'used_at' => $this->usedAt ? $this->usedAt->format('Y-m-d H:i:s') : null,
            'attempts' => $this->attempts,
            'ip_address' => $this->ipAddress,
            'user_agent' => $this->userAgent ? substr($this->userAgent, 0, 100) : null,
            'remaining_minutes' => $this->getRemainingMinutes(),
            'is_valid' => $this->isValid(),
        ];
    }
}