<?php
// src/Controller/SecurityController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(Request $request, AuthenticationUtils $authenticationUtils): Response
    {
        $error = null;
        $success = null;

        // Vérifier si un mot de passe a été soumis
        if ($request->isMethod('POST')) {
            $password = $request->request->get('password');

            if ($password === 'manel2003') {
                // Stocker en session que l'utilisateur est connecté
                $request->getSession()->set('veterinary_authenticated', true);

                // Rediriger vers le backoffice
                return $this->redirectToRoute('app_medicament_index');
            } else {
                $error = 'Code d\'accès incorrect.';
            }
        }

        return $this->render('security/login.html.twig', [
            'error' => $error,
            'success' => $success
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(Request $request): Response
    {
        // Supprimer la session
        $request->getSession()->remove('veterinary_authenticated');

        return $this->redirectToRoute('app_client_interface');
    }
}