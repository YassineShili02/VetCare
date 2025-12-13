<?php
// src/Controller/DashboardController.php

namespace App\Controller;

use App\Repository\MedicamentRepository;
use App\Repository\TraitementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard')]
    public function index(): Response
    {
        return $this->render('dashboard/index.html.twig');
    }

    #[Route('/client', name: 'app_client_interface')]
    public function clientInterface(MedicamentRepository $medicamentRepository, TraitementRepository $traitementRepository): Response
    {
        return $this->render('client/interface.html.twig', [
            'medicaments' => $medicamentRepository->findAll(),
            'traitements' => $traitementRepository->findAll(),
        ]);
    }

    #[Route('/medicament', name: 'app_redirect_medicament')]
    public function redirectMedicament(): Response
    {
        return $this->redirectToRoute('app_medicament_index');
    }

    #[Route('/traitement', name: 'app_redirect_traitement')]
    public function redirectTraitement(): Response
    {
        return $this->redirectToRoute('app_traitement_index');
    }
}