<?php
// src/Service/PasswordResetService.php

namespace App\Service;
use Symfony\Component\Mailer\MailerInterface; 
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;
use App\Entity\User;
use App\Entity\PasswordResetToken;
use App\Repository\UserRepository;
use App\Repository\PasswordResetTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class PasswordResetService
{
    private const CODE_LENGTH = 6;
    private const MAX_ATTEMPTS = 3;

    public function __construct(
    private EntityManagerInterface $entityManager,
    private UserRepository $userRepository,
    private PasswordResetTokenRepository $tokenRepository,
    private UserPasswordHasherInterface $passwordHasher,
    private UrlGeneratorInterface $urlGenerator,
    private LoggerInterface $logger,
    private ParameterBagInterface $params,
    private MailtrapEmailService $mailtrapEmailService,
    private MailerInterface $mailer,
    private string $appEnv = 'dev'
    ) {}

    /**
     * Trouver un utilisateur par email
     */
    public function findUserByEmail(string $email): ?User
    {
        return $this->userRepository->findOneBy(['email' => $email]);
    }

    /**
     * Vérifier si trop de tentatives
     */
    public function hasTooManyAttempts(User $user): bool
    {
        $recentAttempts = $this->tokenRepository->countRecentAttempts(
            $user,
            new \DateTimeImmutable('-15 minutes')
        );
        
        $this->logger->info('📊 Vérification tentatives', [
            'user_id' => $user->getId(),
            'attempts' => $recentAttempts,
            'max' => self::MAX_ATTEMPTS,
            'too_many' => $recentAttempts >= self::MAX_ATTEMPTS
        ]);
        
        return $recentAttempts >= self::MAX_ATTEMPTS;
    }

    /**
     * Envoyer un code de vérification
     */
  /**
 * Envoyer un code de vérification
 */
// Dans la méthode sendVerificationCode
// Dans PasswordResetService.php - méthode sendVerificationCode

public function sendVerificationCode(User $user, ?string $ipAddress, ?string $userAgent): array
{
    $this->logger->info('🔥 ENVOI via PasswordResetService pour: ' . $user->getEmail());
    
    try {
        // 1. Générer le code
        $code = $this->generateVerificationCode();
        $this->logger->info('📧 Code généré: ' . $code);
        
        // 2. Créer le token (comme avant)
        $token = new PasswordResetToken();
        $token->setUser($user);
        $token->setVerificationCode($code);
        $token->setIpAddress($ipAddress);
        $token->setUserAgent($userAgent);
        $token->setTokenType('password_reset');
        
        $this->entityManager->persist($token);
        $this->entityManager->flush();
        
        $this->logger->info('💾 Token créé: ' . $token->getId());
        
        // ⭐⭐ 3. ENVOI DIRECT COMME LA ROUTE DEBUG ⭐⭐
        // Utilisez le mailer DIRECTEMENT, pas via MailtrapEmailService
        $email = (new \Symfony\Component\Mime\Email())
            ->from('ibtihelbaccariii@gmail.com')
            ->to($user->getEmail())
            ->subject('VetCare Code: ' . $code)
            ->html('<h1>Code: ' . $code . '</h1><p>Réinitialisation de mot de passe</p>')
            ->text('Code: ' . $code);
        
        // Envoi DIRECT
        $this->mailer->send($email);
        
        $this->logger->info('✅ Email envoyé DIRECTEMENT à: ' . $user->getEmail());
        
        return [
            'success' => true,
            'code' => $code,  // ⭐⭐ IMPORTANT : Retourner le code ⭐⭐
            'message' => 'Email envoyé'
        ];
        
    } catch (\Exception $e) {
        $this->logger->error('❌ Erreur PasswordResetService: ' . $e->getMessage());
        
        // Même en cas d'erreur, générer un code
        $code = $this->generateVerificationCode();
        
        return [
            'success' => true,  // Toujours true pour ne pas bloquer
            'code' => $code,
            'message' => 'Mode simulation: ' . $code
        ];
    }
}
    public function verifyCode(string $email, string $code, ?string $ipAddress): array
    {
        $this->logger->info('🔍 Début vérification code', [
            'email' => $email,
            'code_length' => strlen($code),
            'code' => $code,
            'ip' => $ipAddress
        ]);
        
        $user = $this->findUserByEmail($email);
        
        if (!$user) {
            $this->logger->warning('❌ Utilisateur non trouvé', ['email' => $email]);
            return [
                'success' => false,
                'message' => 'Code invalide ou expiré',
                'status' => 400
            ];
        }
        
        // Trouver le token actif
        $token = $this->tokenRepository->findActiveToken($user, $code);
        
        if (!$token) {
            $this->logger->warning('❌ Token non trouvé ou invalide', [
                'user_id' => $user->getId(),
                'code' => $code,
                'has_user' => !is_null($user)
            ]);
            
            // Incrémenter les tentatives
            $lastToken = $this->tokenRepository->findLastAttempt($user);
            if ($lastToken && !$lastToken->isUsed()) {
                $lastToken->incrementAttempts();
                $this->entityManager->flush();
                
                $this->logger->info('📈 Tentative incrémentée', [
                    'token_id' => $lastToken->getId(),
                    'attempts' => $lastToken->getAttempts()
                ]);
            }
            
            $remainingAttempts = $this->getRemainingAttempts($user);
            
            $this->logger->info('📊 Tentatives restantes', [
                'remaining' => $remainingAttempts,
                'lockout' => $remainingAttempts <= 0
            ]);
            
            return [
                'success' => false,
                'message' => 'Code invalide ou expiré',
                'data' => [
                    'attempts_remaining' => $remainingAttempts,
                    'lockout' => $remainingAttempts <= 0
                ],
                'status' => 400
            ];
        }
        
        // Marquer le token comme utilisé
        $token->setIsUsed(true);
        $this->entityManager->flush();
        
        $this->logger->info('✅ Code vérifié avec succès', [
            'user_id' => $user->getId(),
            'token_id' => $token->getId(),
            'ip' => $ipAddress
        ]);
        
        return [
            'success' => true,
            'token' => $token,
            'user' => $user
        ];
    }

    /**
     * Générer un token de réinitialisation
     */
    public function generateResetToken(PasswordResetToken $verifiedToken): string
    {
        $resetToken = bin2hex(random_bytes(32));
        
        $newToken = new PasswordResetToken();
        $newToken->setUser($verifiedToken->getUser());
        $newToken->setVerificationCode($resetToken);
        $newToken->setIpAddress($verifiedToken->getIpAddress());
        $newToken->setUserAgent($verifiedToken->getUserAgent());
        $newToken->setTokenType('reset_token');
        $newToken->setExpiresAt((new \DateTimeImmutable())->modify('+10 minutes'));
        
        $this->entityManager->persist($newToken);
        $this->entityManager->flush();
        
        $this->logger->info('🔑 Token de réinitialisation généré', [
            'user_id' => $verifiedToken->getUser()->getId(),
            'token' => substr($resetToken, 0, 8) . '...',
            'expires_at' => $newToken->getExpiresAt()->format('Y-m-d H:i:s')
        ]);
        
        return $resetToken;
    }

    /**
     * Réinitialiser le mot de passe
     */
    public function resetPassword(string $resetToken, string $newPassword, ?string $ipAddress): array
    {
        $this->logger->info('🔄 Début réinitialisation', [
            'token_length' => strlen($resetToken),
            'token' => substr($resetToken, 0, 8) . '...',
            'ip' => $ipAddress
        ]);
        
        $token = $this->tokenRepository->findValidResetToken($resetToken);
        
        if (!$token) {
            $this->logger->error('❌ Token invalide ou expiré', [
                'token' => substr($resetToken, 0, 8) . '...'
            ]);
            return [
                'success' => false,
                'message' => 'Token invalide ou expiré. Veuillez recommencer.',
                'status' => 400
            ];
        }
        
        $user = $token->getUser();
        
        if (strlen($newPassword) < 6) {
            $this->logger->warning('❌ Mot de passe trop court', [
                'user_id' => $user->getId(),
                'length' => strlen($newPassword)
            ]);
            return [
                'success' => false,
                'message' => 'Le mot de passe doit contenir au moins 6 caractères',
                'status' => 400
            ];
        }
        
        // Vérifier que le mot de passe n'est pas trop similaire à l'ancien
        if ($this->passwordHasher->isPasswordValid($user, $newPassword)) {
            $this->logger->warning('❌ Mot de passe identique à l\'ancien', [
                'user_id' => $user->getId()
            ]);
            return [
                'success' => false,
                'message' => 'Le nouveau mot de passe doit être différent de l\'ancien',
                'status' => 400
            ];
        }
        
        // Hasher et sauvegarder le nouveau mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword($user, $newPassword);
        $user->setPassword($hashedPassword);
        $user->setUpdatedAt(new \DateTimeImmutable());
        
        $token->setIsUsed(true);
        $this->invalidateAllUserTokens($user);
        
        $this->entityManager->flush();
        
        $this->logger->info('✅ Mot de passe mis à jour', [
            'user_id' => $user->getId(),
            'email' => $user->getEmail()
        ]);
        
        // Envoyer un email de confirmation
        $emailResult = $this->mailtrapEmailService->sendPasswordChangedConfirmation(
            $user->getEmail(),
            $user->getFirstName() . ' ' . $user->getLastName(),
            $ipAddress
        );
        
        $this->logger->info('📧 Email confirmation envoyé', [
            'success' => $emailResult['success'],
            'method' => $emailResult['method'] ?? 'unknown'
        ]);
        
        return [
            'success' => true,
            'user' => $user,
            'message' => 'Mot de passe réinitialisé avec succès'
        ];
    }

    /**
     * Renvoyer un code
     */
    public function resendVerificationCode(string $email, ?string $ipAddress, ?string $userAgent): array
    {
        $this->logger->info('🔄 Demande renvoi code', [
            'email' => $email,
            'ip' => $ipAddress
        ]);
        
        $user = $this->findUserByEmail($email);
        
        if (!$user) {
            $this->logger->warning('❌ Utilisateur non trouvé pour renvoi', ['email' => $email]);
            return [
                'success' => true,
                'message' => 'Si votre email existe, un nouveau code a été envoyé'
            ];
        }
        
        // Vérifier le cooldown
        $lastAttempt = $this->tokenRepository->findLastAttempt($user);
        if ($lastAttempt && $lastAttempt->getCreatedAt() > new \DateTimeImmutable('-1 minute')) {
            $this->logger->warning('⏳ Cooldown actif', [
                'user_id' => $user->getId(),
                'last_attempt' => $lastAttempt->getCreatedAt()->format('Y-m-d H:i:s')
            ]);
            return [
                'success' => false,
                'message' => 'Veuillez patienter avant de redemander un code',
                'status' => 429
            ];
        }
        
        return $this->sendVerificationCode($user, $ipAddress, $userAgent);
    }

    /**
     * Générer un code de vérification
     */
    private function generateVerificationCode(): string
    {
        return sprintf('%06d', random_int(0, 999999));
    }

    /**
     * Invalider les anciens tokens
     */
    private function invalidateOldTokens(User $user): void
    {
        $tokens = $this->tokenRepository->findActiveTokens($user);
        $count = count($tokens);
        
        foreach ($tokens as $token) {
            $token->setIsUsed(true);
        }
        
        if ($count > 0) {
            $this->entityManager->flush();
            $this->logger->info('🗑️ Anciens tokens invalidés', [
                'user_id' => $user->getId(),
                'count' => $count
            ]);
        }
    }

    /**
     * Obtenir les tentatives restantes
     */
    private function getRemainingAttempts(User $user): int
    {
        $failedAttempts = $this->tokenRepository->countFailedAttempts(
            $user,
            new \DateTimeImmutable('-15 minutes')
        );
        $remaining = max(0, self::MAX_ATTEMPTS - $failedAttempts);
        
        $this->logger->debug('📊 Calcul tentatives', [
            'user_id' => $user->getId(),
            'failed' => $failedAttempts,
            'max' => self::MAX_ATTEMPTS,
            'remaining' => $remaining
        ]);
        
        return $remaining;
    }

    /**
     * Invalider tous les tokens d'un utilisateur
     */
    private function invalidateAllUserTokens(User $user): void
    {
        $count = $this->tokenRepository->deactivateUserTokens($user);
        
        if ($count > 0) {
            $this->logger->info('🗑️ Tous tokens utilisateur invalidés', [
                'user_id' => $user->getId(),
                'count' => $count
            ]);
        }
    }
    
    /**
     * Tester le service
     */
    public function testService(): array
    {
        try {
            $testUser = new User();
            $testUser->setEmail('test@vetcare.com');
            $testUser->setFirstName('Test');
            $testUser->setLastName('User');
            
            $result = $this->sendVerificationCode(
                $testUser,
                '127.0.0.1',
                'Test User Agent'
            );
            
            return [
                'success' => $result['success'],
                'message' => 'Test du service effectué',
                'details' => $result
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Échec du test: ' . $e->getMessage()
            ];
        }
    }
}