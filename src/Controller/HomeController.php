<?php
// src/Controller/HomeController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\ContactType; // Si tu as un formulaire de contact
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig');
    }

    #[Route('/about', name: 'app_about')]
    public function about(): Response
    {
        return $this->render('home/about.html.twig');
    }

    // src/Controller/HomeController.php
    #[Route('/services', name: 'services')]
    public function services(): Response
    {
        return $this->render('home/services.html.twig');
    }

    #[Route('/actualites', name: 'app_actualites')]
    public function actualites(): Response
    {
        return $this->render('home/actualites.html.twig');
    }

    #[Route('/contact', name: 'app_contact')]
    public function contact(Request $request, MailerInterface $mailer): Response
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // Exemple d'envoi d'email
            $email = (new Email())
                ->from($data['email'])
                ->to('contact@vetcare.com')
                ->subject('Nouveau message depuis le formulaire de contact')
                ->html(
                    "Nom: {$data['nom']}<br>" .
                    "Email: {$data['email']}<br>" .
                    "Téléphone: {$data['telephone']}<br>" .
                    "Sujet: {$data['sujet']}<br>" .
                    "Message: {$data['message']}"
                );

            $mailer->send($email);

            $this->addFlash('success', 'Votre message a été envoyé avec succès !');
            return $this->redirectToRoute('app_contact');
        }

        return $this->render('home/contact.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
