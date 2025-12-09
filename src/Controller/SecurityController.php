<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends AbstractController
{
    private const VETERINARY_PASSWORD = 'manel2003';

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
            $this->addFlash('success', 'Vous êtes déjà connecté en tant que vétérinaire.');
            return $this->redirectToRoute('app_medicament_index');
        }

        if ($request->isMethod('POST')) {
            $password = $request->request->get('password');

            if ($password === self::VETERINARY_PASSWORD) {
                // CONNEXION VÉTÉRINAIRE RÉUSSIE
                $session->set('veterinary_logged_in', true);
                $session->set('veterinary_login_time', time());

                $this->addFlash('success', 'Connexion vétérinaire réussie ! Bienvenue.');
                return $this->redirectToRoute('app_medicament_index');
            } else {
                $this->addFlash('error', 'Mot de passe incorrect.');
            }
        }

        // Afficher directement le formulaire de connexion
        return new Response('
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Accès Vétérinaire - VetCare</title>
            <style>
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }

                body {
                    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
                    background: #ffffff;
                    min-height: 100vh;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    color: #333333;
                    padding: 2rem;
                }

                .login-container {
                    width: 100%;
                    max-width: 400px;
                    padding: 1rem;
                }

                .login-box {
                    background: #f8f9fa;
                    padding: 2.5rem 2rem;
                    border-radius: 10px;
                    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
                    border: 1px solid #dee2e6;
                    text-align: center;
                }

                .login-icon {
                    font-size: 3rem;
                    margin-bottom: 1rem;
                    color: #6c757d;
                }

                .login-title {
                    font-size: 1.8rem;
                    margin-bottom: 0.5rem;
                    color: #2c3e50;
                }

                .login-subtitle {
                    color: #6c757d;
                    margin-bottom: 2rem;
                    font-size: 1rem;
                }

                .form-group {
                    margin-bottom: 1.5rem;
                    text-align: left;
                }

                .form-label {
                    display: block;
                    margin-bottom: 0.5rem;
                    font-weight: 600;
                    color: #495057;
                }

                .form-control {
                    width: 100%;
                    padding: 0.75rem;
                    border: 1px solid #ced4da;
                    border-radius: 4px;
                    font-size: 1rem;
                    background: #ffffff;
                    color: #333333;
                    transition: all 0.3s;
                }

                .form-control:focus {
                    outline: none;
                    border-color: #6c757d;
                    box-shadow: 0 0 0 2px rgba(108, 117, 125, 0.25);
                }

                .btn {
                    display: inline-block;
                    padding: 0.75rem 2rem;
                    border: none;
                    border-radius: 4px;
                    text-decoration: none;
                    font-size: 1rem;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    color: white;
                    width: 100%;
                    font-weight: 600;
                    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
                }

                .btn:hover {
                    background: linear-gradient(135deg, #5a6268 0%, #495057 100%);
                    transform: translateY(-1px);
                }

                .alert {
                    padding: 1rem;
                    border-radius: 4px;
                    margin-bottom: 1rem;
                    text-align: left;
                    font-size: 0.9rem;
                }

                .alert-error {
                    background-color: #f8d7da;
                    border: 1px solid #f5c6cb;
                    color: #721c24;
                }

                .alert-success {
                    background-color: #d4edda;
                    border: 1px solid #c3e6cb;
                    color: #155724;
                }

                .back-link {
                    display: inline-block;
                    margin-top: 1.5rem;
                    color: #6c757d;
                    text-decoration: none;
                    transition: color 0.3s;
                    font-weight: 500;
                }

                .back-link:hover {
                    color: #495057;
                    text-decoration: underline;
                }
            </style>
        </head>
        <body>
            <div class="login-container">
                <div class="login-box">
                    <div class="login-icon">🔐</div>
                    <h1 class="login-title">Accès Vétérinaire</h1>
                    <p class="login-subtitle">Espace sécurisé réservé aux vétérinaires</p>

                    ' . ($request->isMethod('POST') ? '
                    <div class="alert alert-error">
                        Mot de passe incorrect
                    </div>
                    ' : '') . '

                    <form method="post">
                        <div class="form-group">
                            <label class="form-label">Mot de passe vétérinaire :</label>
                            <input type="password" name="password" class="form-control" required
                                   placeholder="Entrez le mot de passe d\'accès">
                        </div>

                        <button type="submit" class="btn">
                            Se connecter
                        </button>
                    </form>

                    <a href="' . $this->generateUrl('app_home') . '" class="back-link">
                        ← Retour à l\'accueil
                    </a>
                </div>
            </div>

            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    const passwordInput = document.querySelector("input[name=\'password\']");
                    if (passwordInput) {
                        passwordInput.focus();
                    }

                    const form = document.querySelector("form");
                    const button = document.querySelector(".btn");

                    if (form && button) {
                        form.addEventListener("submit", function() {
                            button.innerHTML = "Connexion en cours...";
                            button.disabled = true;
                        });
                    }
                });
            </script>
        </body>
        </html>');
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(SessionInterface $session): Response
    {
        $session->clear();
        $this->addFlash('success', 'Déconnexion réussie. À bientôt !');
        return $this->redirectToRoute('app_home');
    }

    /**
     * Méthode pour vérifier l'accès vétérinaire
     */
    public static function isVeterinary(SessionInterface $session): bool
    {
        return $session->get('veterinary_logged_in', false);
    }
}