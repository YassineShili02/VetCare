<?php
<<<<<<< HEAD
namespace App\Controller;

use App\Repository\MedicamentRepository;
use App\Repository\TraitementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
=======

namespace App\Controller;

use App\Form\ContactType;
use App\Repository\MedicamentRepository;
use App\Repository\TraitementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
>>>>>>> 3e2030c75b9f4a89f35ca6db7ad15e627d1d3c9e
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig');
    }

<<<<<<< HEAD
    #[Route('/client', name: 'app_client_interface')]
    public function clientInterface(MedicamentRepository $medicamentRepository, TraitementRepository $traitementRepository): Response
    {
=======
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

    #[Route('/client', name: 'app_client_interface')]
    public function clientInterface(
        MedicamentRepository $medicamentRepository,
        TraitementRepository $traitementRepository
    ): Response {
>>>>>>> 3e2030c75b9f4a89f35ca6db7ad15e627d1d3c9e
        return $this->render('client/interface.html.twig', [
            'medicaments' => $medicamentRepository->findAll(),
            'traitements' => $traitementRepository->findAll(),
        ]);
    }
<<<<<<< HEAD
}
=======
}
>>>>>>> 3e2030c75b9f4a89f35ca6db7ad15e627d1d3c9e
