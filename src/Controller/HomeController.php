<?php

namespace App\Controller;

use App\Form\ContactType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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

    #[Route('/blog', name: 'app_blog')]
    public function blog(): Response
    {
        return $this->render('home/blog.html.twig');
    }

    #[Route('/services', name: 'app_services')]
    public function services(): Response
    {
        return $this->render('home/services.html.twig');
    }

    #[Route('/contact', name: 'app_contact')]
    public function contact(Request $request): Response
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash('success', 'Votre message a été envoyé avec succès !');
            return $this->redirectToRoute('app_contact');
        }

        return $this->render('home/contact.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}