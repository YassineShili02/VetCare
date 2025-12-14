<?php
// src/Controller/SecurityController.php

namespace App\Controller;

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;
use App\Entity\User;
use App\Form\ForgotPasswordType;
use App\Form\ResetPasswordType;
use App\Service\PasswordResetService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SecurityController extends AbstractController
{
    private LoggerInterface $logger;
    private PasswordResetService $passwordResetService;
    
    public function __construct(LoggerInterface $logger, PasswordResetService $passwordResetService)
    {
        $this->logger = $logger;
        $this->passwordResetService = $passwordResetService;
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            $this->addFlash('info', 'Vous êtes déjà connecté.');
            return $this->redirectToRoute('app_user_index');
        }

        $error = $authenticationUtils->getLastAuthenticationError();

        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // controller can be blank: it will never be called!
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    // ⭐⭐ ASSUREZ-VOUS QUE CETTE ROUTE EXISTE ⭐⭐
#[Route('/forgot-password', name: 'app_forgot_password', methods: ['GET', 'POST'])]
public function forgotPassword(Request $request): Response
{
    $this->logger->info('=== FORGOT PASSWORD ACCESSED ===');

    if ($this->getUser()) {
        $this->addFlash('info', 'Vous êtes déjà connecté.');
        return $this->redirectToRoute('app_user_index');
    }

    $form = $this->createForm(ForgotPasswordType::class);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $email = $form->get('email')->getData();
        $this->logger->info('Email submitted: ' . $email);

        $user = $this->passwordResetService->findUserByEmail($email);
        
// Dans la méthode forgotPassword
if ($user) {
    $this->logger->info('📧 === DÉBUT ENVOI NORMAL ===');
    $this->logger->info('📧 Utilisateur: ' . $user->getEmail() . ' (ID: ' . $user->getId() . ')');
    
    $ipAddress = $request->getClientIp();
    $userAgent = $request->headers->get('User-Agent');
    
    $this->logger->info('📧 Appel PasswordResetService...');
    
    $result = $this->passwordResetService->sendVerificationCode(
        $user, 
        $ipAddress, 
        $userAgent
    );
    
    $this->logger->info('📧 Résultat COMPLET: ' . json_encode($result, JSON_PRETTY_PRINT));
    
    // ⭐⭐ FORCER le stockage du code ⭐⭐
    $session = $request->getSession();
    $session->set('reset_email', $user->getEmail());
    
    if (isset($result['code'])) {
        $session->set('reset_code', $result['code']);
        $this->logger->info('📧 Code stocké en session: ' . $result['code']);
    } else {
        $backupCode = sprintf('%06d', rand(100000, 999999));
        $session->set('reset_code', $backupCode);
        $this->logger->info('📧 Code de secours stocké: ' . $backupCode);
    }
    
    if ($result['success']) {
        $this->addFlash('success', '✅ Code envoyé à votre email !');
    } else {
        $this->addFlash('warning', '⚠️ ' . ($result['message'] ?? 'Problème technique'));
    }
    
    return $this->redirectToRoute('app_code_sent');
            
        } else {
            $this->logger->info('User not found for email: ' . $email);
            
            // Stocker quand même pour l'UX
            $session = $request->getSession();
            $session->set('reset_email', $email);
            // Générer un code fictif
            $fakeCode = sprintf('%06d', rand(100000, 999999));
            $session->set('reset_code', $fakeCode);
            
            $this->addFlash('info', 'Si cet email existe, un code a été envoyé.');
            return $this->redirectToRoute('app_code_sent');
        }
    }

    return $this->render('security/forgot_password.html.twig', [
        'form' => $form->createView(),
    ]);
}

// ⭐⭐ CORRIGEZ LA MÉTHODE codeSent POUR NE PAS ENVOYER debug_code ⭐⭐
#[Route('/code-sent', name: 'app_code_sent', methods: ['GET'])]
public function codeSent(Request $request): Response
{
    $email = $request->getSession()->get('reset_email');
    $code = $request->getSession()->get('reset_code');
    
    if (!$email) {
        return $this->redirectToRoute('app_forgot_password');
    }
    
    $this->logger->info('Code sent page - Email: ' . $email . ', Code: ' . ($code ?? 'NULL'));
    
    // Version avec debug_code conditionnellement
    $data = ['email' => $email];
    
    if ($this->getParameter('kernel.environment') === 'dev' && $code) {
        $data['debug_code'] = $code;
        $this->logger->info('Mode DEV - Code disponible: ' . $code);
    }
    
    return $this->render('security/code_sent.html.twig', $data);
}

#[Route('/verify-code', name: 'app_verify_code', methods: ['GET', 'POST'])]
public function verifyCode(Request $request): Response
{
    $this->logger->info('=== VERIFY CODE ACCESSED ===');

    if ($this->getUser()) {
        $this->addFlash('info', 'Vous êtes déjà connecté.');
        return $this->redirectToRoute('app_user_index');
    }

    $session = $request->getSession();
    $email = $session->get('reset_email');
    $storedCode = $session->get('reset_code');
    
    $this->logger->info('Session email: ' . ($email ?? 'NULL'));
    $this->logger->info('Session code: ' . ($storedCode ?? 'NULL'));
    
    if (!$email) {
        $this->addFlash('error', 'Session expirée. Veuillez recommencer.');
        return $this->redirectToRoute('app_forgot_password');
    }

    if ($request->isMethod('POST')) {
        $submittedCode = $request->request->get('code');
        $this->logger->info('Code submitted: ' . $submittedCode);
        
        if (empty($submittedCode)) {
            $this->addFlash('error', 'Veuillez saisir le code.');
            return $this->redirectToRoute('app_verify_code');
        }
        
        // Nettoyer le code
        $submittedCode = preg_replace('/\s+/', '', $submittedCode);
        
        if (strlen($submittedCode) !== 6 || !is_numeric($submittedCode)) {
            $this->addFlash('error', 'Le code doit contenir exactement 6 chiffres.');
            return $this->redirectToRoute('app_verify_code');
        }
        
        // Vérification
        if ($storedCode && $submittedCode === $storedCode) {
            $session->set('reset_verified', true);
            $this->addFlash('success', '✅ Code vérifié avec succès!');
            
            return $this->redirectToRoute('app_password_reset_form');
        } else {
            $this->addFlash('error', '❌ Code incorrect. Réessayez.');
            // Afficher le code stocké en mode dev pour aider
            if ($this->getParameter('kernel.environment') === 'dev' && $storedCode) {
                $this->addFlash('info', '🛠️ Mode DEV - Code attendu: ' . $storedCode);
            }
            return $this->redirectToRoute('app_verify_code');
        }
    }

    // GET - afficher le formulaire
    return $this->render('security/verify_code.html.twig', [
        'email' => $email,
        // Toujours passer le code en dev pour debugging
        'debug_code' => $this->getParameter('kernel.environment') === 'dev' ? $storedCode : null
    ]);
}

#[Route('/test-full-flow/{email}', name: 'app_test_full_flow')]
public function testFullFlow(
    string $email,
    Request $request,
    PasswordResetService $passwordResetService,
    LoggerInterface $logger
): Response {
    $logger->info('🧪 TEST FULL FLOW for: ' . $email);
    
    // 1. Chercher utilisateur
    $user = $passwordResetService->findUserByEmail($email);
    
    if (!$user) {
        $logger->warning('User not found, creating temporary user');
        // Créer un utilisateur temporaire
        $user = new User();
        $user->setEmail($email);
        $user->setFirstName('Test');
        $user->setLastName('User');
        $user->setPassword('temp');
        $user->setRoles(['ROLE_USER']);
        
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();
        
        $logger->info('Temporary user created: ' . $user->getId());
    }
    
    // 2. Envoyer le code
    $result = $passwordResetService->sendVerificationCode(
        $user,
        '127.0.0.1',
        'Test Browser'
    );
    
    $logger->info('Send result: ' . json_encode($result));
    
    // 3. Stocker en session
    $session = $request->getSession();
    $session->set('reset_email', $email);
    
    if (isset($result['code'])) {
        $session->set('reset_code', $result['code']);
        $code = $result['code'];
    } else {
        $code = '123456';
        $session->set('reset_code', $code);
    }
    
    // 4. Rediriger
    $this->addFlash('success', '🧪 Test flux - Code: ' . $code);
    
    return $this->redirectToRoute('app_verify_code');
}

    #[Route('/password-reset-form', name: 'app_password_reset_form', methods: ['GET', 'POST'])]
    public function passwordResetForm(
        Request $request, 
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $session = $request->getSession();
        $email = $session->get('reset_email');
        $isVerified = $session->get('reset_verified');
        
        $this->logger->info('Password reset form - Email: ' . ($email ?? 'NULL'));
        $this->logger->info('Is verified: ' . ($isVerified ? 'YES' : 'NO'));
        
        // Vérifier que l'utilisateur a bien vérifié son code
        if (!$email || !$isVerified) {
            $this->addFlash('error', 'Vous devez d\'abord vérifier votre code de sécurité.');
            return $this->redirectToRoute('app_forgot_password');
        }
        
        // Trouver l'utilisateur par email
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        
// Ajoutez ce débogage dans SecurityController.php, méthode forgotPassword :

if ($user) {
    $this->logger->info('📧 User found: ' . $user->getEmail());
    
    $ipAddress = $request->getClientIp();
    $userAgent = $request->headers->get('User-Agent');
    
    $result = $this->passwordResetService->sendVerificationCode(
        $user, 
        $ipAddress, 
        $userAgent
    );
    
    // ⭐⭐ LOG DÉTAILLÉ DU RÉSULTAT ⭐⭐
    $this->logger->info('📧 RESULTAT COMPLET: ' . json_encode($result, JSON_PRETTY_PRINT));
    
    // Stockage FORCÉ en session
    $session = $request->getSession();
    $session->set('reset_email', $user->getEmail());
    
    if (isset($result['code'])) {
        $session->set('reset_code', $result['code']);
        $this->logger->info('📧 CODE STOCKÉ: ' . $result['code']);
    } else {
        // Code de secours
        $backupCode = sprintf('%06d', rand(100000, 999999));
        $session->set('reset_code', $backupCode);
        $this->logger->info('📧 CODE DE SECOURS STOCKÉ: ' . $backupCode);
    }
    
}

        
        // Créer le formulaire de réinitialisation
        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $newPassword = $data['newPassword'];
            
            $this->logger->info('New password submitted for user: ' . $user->getEmail());
            
            try {
                // Hasher le nouveau mot de passe
                $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
                
                // Vérifier que le nouveau mot de passe est différent de l'ancien
                if ($passwordHasher->isPasswordValid($user, $newPassword)) {
                    $this->addFlash('error', 'Le nouveau mot de passe doit être différent de l\'ancien.');
                    return $this->redirectToRoute('app_password_reset_form');
                }
                
                // Mettre à jour le mot de passe
                $user->setPassword($hashedPassword);
                $user->setUpdatedAt(new \DateTimeImmutable());
                
                $entityManager->flush();
                
                // Vérifier que le nouveau mot de passe fonctionne
                $isValid = $passwordHasher->isPasswordValid($user, $newPassword);
                
                if (!$isValid) {
                    $this->logger->error('Password validation failed after reset for user: ' . $user->getId());
                    throw new \Exception('Échec de la validation du nouveau mot de passe.');
                }
                
                // Nettoyer la session
                $session->remove('reset_email');
                $session->remove('reset_code');
                $session->remove('reset_verified');
                $session->remove('reset_token');
                
                $this->logger->info('Password successfully reset for user: ' . $user->getEmail());
                
                $this->addFlash('success', '✅ Votre mot de passe a été réinitialisé avec succès ! Vous pouvez maintenant vous connecter.');
                
                // Rediriger vers la page de connexion
                return $this->redirectToRoute('app_login');
                
            } catch (\Exception $e) {
                $this->logger->error('Password reset error: ' . $e->getMessage());
                $this->addFlash('error', '❌ Erreur lors de la réinitialisation: ' . $e->getMessage());
            }
        }
        
        return $this->render('security/password_reset_form.html.twig', [
            'form' => $form->createView(),
            'email' => $email,
            'user' => $user
        ]);
    }

    #[Route('/reset-password/{token}', name: 'app_reset_password_with_token', methods: ['GET', 'POST'])]
    public function resetPasswordWithToken(
        Request $request,
        string $token,
        PasswordResetService $passwordResetService,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        // Cette méthode reste inchangée
        $tokenEntity = $passwordResetService->validateResetToken($token);
        
        if (!$tokenEntity) {
            $this->addFlash('error', 'Token invalide ou expiré.');
            return $this->redirectToRoute('app_forgot_password');
        }
        
        $user = $tokenEntity->getUser();
        
        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $newPassword = $data['newPassword'];
            
            try {
                $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
                $user->setPassword($hashedPassword);
                $user->setUpdatedAt(new \DateTimeImmutable());
                
                $tokenEntity->setIsUsed(true);
                $tokenEntity->setUsedAt(new \DateTimeImmutable());
                
                $entityManager->flush();
                
                $this->addFlash('success', 'Votre mot de passe a été réinitialisé avec succès.');
                return $this->redirectToRoute('app_login');
                
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la réinitialisation: ' . $e->getMessage());
            }
        }
        
        return $this->render('security/reset_password.html.twig', [
            'resetForm' => $form->createView(),
            'token' => $token,
        ]);
    }

   #[Route('/resend-code', name: 'app_resend_code')]
public function resendCode(Request $request): Response
{
    $email = $request->getSession()->get('reset_email');
    
    if (!$email) {
        $this->addFlash('error', 'Session expirée. Veuillez recommencer.');
        return $this->redirectToRoute('app_forgot_password');
    }

    $user = $this->passwordResetService->findUserByEmail($email);
    
    if ($user) {
        $ipAddress = $request->getClientIp();
        $userAgent = $request->headers->get('User-Agent');
        
        $result = $this->passwordResetService->sendVerificationCode($user, $ipAddress, $userAgent);
        
        if ($result['success']) {
            $this->addFlash('success', '✅ Un nouveau code a été envoyé.');
            
            // Mettre à jour le code en session
            if (isset($result['code'])) {
                $request->getSession()->set('reset_code', $result['code']);
            }
        } else {
            $this->addFlash('error', '❌ ' . $result['message']);
        }
    } else {
        $this->addFlash('info', 'Si votre email existe, un nouveau code a été envoyé.');
    }

    return $this->redirectToRoute('app_verify_code');
}
    #[Route('/reset-password', name: 'app_reset_password')]
    public function resetPassword(Request $request): Response
    {
        $this->addFlash('info', 'Veuillez utiliser le formulaire "Mot de passe oublié".');
        return $this->redirectToRoute('app_forgot_password');
    }

    #[Route('/debug-forgot/{email}', name: 'app_debug_forgot')]
    public function debugForgot(
        string $email,
        Request $request,
        \App\Service\MailtrapEmailService $emailService,
        LoggerInterface $logger
    ): Response {
        $logger->info('=== DEBUG FORGOT PASSWORD ===');
        $logger->info('Email: ' . $email);
        
        // Test direct du service
        $result = $emailService->sendVerificationCode(
            $email,
            '999999',
            'Debug User'
        );
        
        $logger->info('Service result: ' . json_encode($result));
        
        // Stocker pour tester
        $request->getSession()->set('reset_email', $email);
        $request->getSession()->set('reset_code', '999999');
        
        $this->addFlash('info', 'Résultat service: ' . ($result['success'] ? '✅ OK' : '❌ ECHEC'));
        
        return $this->redirectToRoute('app_verify_code');
    }

    #[Route('/session-info', name: 'app_session_info')]
public function sessionInfo(Request $request): Response
{
    $session = $request->getSession();
    
    $info = [
        'session_id' => $session->getId(),
        'reset_email' => $session->get('reset_email'),
        'reset_code' => $session->get('reset_code'),
        'reset_verified' => $session->get('reset_verified'),
        'all_session' => $session->all()
    ];
    
    return $this->json($info);
}

// Ajoutez dans SecurityController.php

#[Route('/test-email-now/{email}', name: 'app_test_email_now')]
public function testEmailNow(
    string $email,
    Request $request,
    \App\Service\MailtrapEmailService $emailService,
    LoggerInterface $logger
): Response {
    $logger->info('🚀 TEST EMAIL NOW pour: ' . $email);
    
    // 1. Générer un code
    $code = sprintf('%06d', rand(100000, 999999));
    $logger->info('📧 Code généré: ' . $code);
    
    // 2. Envoyer DIRECTEMENT (sans PasswordResetService)
    $result = $emailService->sendVerificationCode($email, $code, 'Test User');
    
    $logger->info('📧 Résultat email: ' . json_encode($result));
    
    // 3. Stocker FORCÉMENT en session
    $session = $request->getSession();
    $session->set('reset_email', $email);
    $session->set('reset_code', $code);
    $session->set('reset_verified', true); // Forcer la vérification
    
    $logger->info('📧 Session stockée - Email: ' . $email . ', Code: ' . $code);
    
    // 4. Afficher les résultats
    if ($result['success']) {
        $this->addFlash('success', '✅ Email envoyé! Code: ' . $code);
        if ($result['method'] === 'SIMULATION') {
            $this->addFlash('warning', '⚠️ Mode simulation activé');
        }
    } else {
        $this->addFlash('error', '❌ Erreur: ' . ($result['error'] ?? 'Inconnue'));
        $this->addFlash('info', '💡 Code disponible: ' . $code);
    }
    
    // 5. Rediriger vers la vérification
    return $this->redirectToRoute('app_verify_code');
}



#[Route('/simple-test-email/{email}', name: 'app_simple_test_email')]
public function simpleTestEmail(string $email, Request $request): Response
{
    $code = sprintf('%06d', rand(100000, 999999));
    
    try {
        // Transport direct
        $transport = Transport::fromDsn('gmail+smtp://ibtihelbaccariii@gmail.com:ywegzfidjojkqgbi@default');
        $mailer = new Mailer($transport);
        
        $emailObj = (new Email())
            ->from('ibtihelbaccariii@gmail.com')
            ->to($email)
            ->subject('Simple Test: ' . $code)
            ->html('<h1>Code: ' . $code . '</h1>');
        
        $mailer->send($emailObj);
        
        $this->addFlash('success', '✅ Email envoyé! Code: ' . $code);
        
    } catch (\Exception $e) {
        $this->addFlash('error', '❌ Erreur: ' . $e->getMessage());
        $this->addFlash('info', '💡 Code disponible: ' . $code);
    }
    
    // Stocker
    $request->getSession()->set('reset_email', $email);
    $request->getSession()->set('reset_code', $code);
    
    return $this->redirectToRoute('app_verify_code');
}
#[Route('/test-same-as-debug/{email}', name: 'app_test_same_as_debug')]
public function testSameAsDebug(string $email, Request $request): Response
{
    $this->logger->info('🧪 TEST SAME AS DEBUG pour: ' . $email);
    
    $code = sprintf('%06d', rand(100000, 999999));
    
    // ⭐⭐ CODE IDENTIQUE À LA ROUTE DEBUG ⭐⭐
    try {
        $emailObj = (new \Symfony\Component\Mime\Email())
            ->from('ibtihelbaccariii@gmail.com')
            ->to($email)
            ->subject('TEST SAME: ' . $code)
            ->html('<h1>Code: ' . $code . '</h1><p>Test identique</p>');
        
        $this->mailer->send($emailObj);
        $success = true;
        $message = '✅ Email envoyé (même code que debug)!';
        
    } catch (\Exception $e) {
        $success = false;
        $message = '❌ Erreur: ' . $e->getMessage();
    }
    
    // Stocker
    $session = $request->getSession();
    $session->set('reset_email', $email);
    $session->set('reset_code', $code);
    
    $this->addFlash($success ? 'success' : 'error', $message);
    $this->addFlash('info', 'Code: ' . $code);
    
    return $this->redirectToRoute('app_verify_code');
}
}