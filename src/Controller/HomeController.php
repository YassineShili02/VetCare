<?php
namespace App\Controller;

use App\Repository\MedicamentRepository;
use App\Repository\TraitementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig');
    }

    #[Route('/client', name: 'app_client_interface')]
    public function clientInterface(MedicamentRepository $medicamentRepository, TraitementRepository $traitementRepository): Response
    {
        return $this->render('client/interface.html.twig', [
            'medicaments' => $medicamentRepository->findAll(),
            'traitements' => $traitementRepository->findAll(),
        ]);
    }
}