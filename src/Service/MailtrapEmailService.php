<?php
namespace App\Service;
use Symfony\Component\Mailer\Transport;  // ⭐⭐ AJOUTEZ CETTE LIGNE ⭐⭐
use Symfony\Component\Mailer\Mailer; 
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Psr\Log\LoggerInterface;

class MailtrapEmailService
{
    private MailerInterface $mailer;
    private LoggerInterface $logger;
    private string $appEnv;
    
    public function __construct(        MailerInterface $mailer, 
        LoggerInterface $logger,
        string $appEnv = 'dev')
    {
        $this->mailer = $mailer;
        $this->logger = $logger;
        $this->appEnv = $appEnv;
    }
// src/Service/MailtrapEmailService.php

public function sendVerificationCode(string $toEmail, string $code, string $recipientName): array
{
    $this->logger->info('🔥 ENVOI SYNCHRONE à: ' . $toEmail);
    
    try {
        // Email simple
        $email = (new Email())
            ->from('ibtihelbaccariii@gmail.com')
            ->to($toEmail)
            ->subject('VetCare Code: ' . $code)
            ->html('<h1>Code: ' . $code . '</h1>')
            ->text('Code: ' . $code);
        
        // ⭐⭐ CE LOG EST CRITIQUE ⭐⭐
        $this->logger->info('📧 Email créé, envoi synchrone en cours...');
        
        // Envoi SYNCHRONE (bloquant)
        $this->mailer->send($email);
        
        $this->logger->info('✅ Email envoyé SYNCHRONE à: ' . $toEmail);
        
        return [
            'success' => true,
            'code' => $code,
            'message' => 'Email envoyé immédiatement'
        ];
        
    } catch (\Exception $e) {
        $this->logger->error('❌ Erreur synchrone: ' . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}
    /**
     * Test simple
     */
    public function testConnection(): array
    {
        try {
            $email = (new Email())
                ->from('ibtihelbaccariii@gmail.com')
                ->to('test@example.com')
                ->subject('Test SMTP ' . date('H:i:s'))
                ->text('Test');
            
            $this->mailer->send($email);
            
            return ['success' => true, 'message' => 'SMTP OK'];
            
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}