<?php

namespace App\Controller;

use App\Repository\TraitementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends AbstractController
{
    private const VETERINARY_PASSWORD = 'manel2003';
    private const CLIENT_PASSWORD = 'manel2003';

    #[Route('/', name: 'app_home')]
    public function home(): Response
    {
        return $this->render('home.html.twig');
    }

    #[Route('/login', name: 'app_login')]
    public function login(Request $request, SessionInterface $session): Response
    {
        // Vérifier si déjà connecté en tant que vétérinaire
        if ($session->get('veterinary_logged_in')) {
            $this->addFlash('info', 'Vous êtes déjà connecté en tant que vétérinaire.');
            return $this->redirectToRoute('app_medicament_index');
        }

        // Vérifier si déjà connecté en tant que client
        if ($session->get('client_logged_in')) {
            $this->addFlash('info', 'Vous êtes déjà connecté en tant que client.');
            return $this->redirectToRoute('app_client_interface');
        }

        if ($request->isMethod('POST')) {
            $password = $request->request->get('password');
            $userType = $request->request->get('user_type', 'veterinary');

            if ($password === self::VETERINARY_PASSWORD && $userType === 'veterinary') {
                // CONNEXION VÉTÉRINAIRE RÉUSSIE
                $session->set('veterinary_logged_in', true);
                $session->set('veterinary_login_time', time());
                $session->remove('client_logged_in');

                $this->addFlash('success', 'Connexion vétérinaire réussie ! Bienvenue.');
                return $this->redirectToRoute('app_medicament_index');
            }
            elseif ($password === self::CLIENT_PASSWORD && $userType === 'client') {
                // CONNEXION CLIENT RÉUSSIE
                $session->set('client_logged_in', true);
                $session->set('client_login_time', time());
                $session->remove('veterinary_logged_in');

                $this->addFlash('success', 'Connexion client réussie ! Bienvenue.');
                return $this->redirectToRoute('app_client_interface');
            }
            else {
                $this->addFlash('error', 'Mot de passe incorrect.');
            }
        }

        return $this->render('security/login.html.twig');
    }

    #[Route('/client', name: 'app_client_interface')]
    public function clientInterface(SessionInterface $session, TraitementRepository $traitementRepository): Response
    {
        // Vérifier si l'utilisateur est connecté en tant que client
        if (!$session->get('client_logged_in')) {
            $this->addFlash('error', 'Veuillez vous connecter pour accéder à l\'interface client.');
            return $this->redirectToRoute('app_login');
        }

        // Récupérer tous les traitements pour l'interface client
        $traitements = $traitementRepository->findAll();

        return $this->render('client/interface.html.twig', [
            'traitements' => $traitements,
            'login_time' => $session->get('client_login_time')
        ]);
    }

    #[Route('/backoffice/traitement/redirect', name: 'app_traitement_index_redirect')]
    public function redirectToTraitement(SessionInterface $session): Response
    {
        // Vérifier si l'utilisateur est connecté en tant que vétérinaire
        if (!$session->get('veterinary_logged_in')) {
            $this->addFlash('error', '🔒 Accès refusé. Cette interface est réservée aux vétérinaires.');
            return $this->redirectToRoute('app_login');
        }

        // Rediriger vers le vrai contrôleur des traitements
        return $this->redirectToRoute('app_traitement_index');
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(SessionInterface $session): Response
    {
        $session->clear();
        $this->addFlash('success', 'Déconnexion réussie. À bientôt !');
        return $this->redirectToRoute('app_home');
    }

    /**
     * Méthodes de vérification d'accès
     */
    public static function isVeterinary(SessionInterface $session): bool
    {
        return $session->get('veterinary_logged_in', false);
    }

    public static function isClient(SessionInterface $session): bool
    {
        return $session->get('client_logged_in', false);
    }
}
