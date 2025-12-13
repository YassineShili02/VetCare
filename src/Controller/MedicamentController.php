<?php
// src/Controller/MedicamentController.php

namespace App\Controller;

use App\Entity\Medicament;
use App\Form\MedicamentType;
use App\Repository\MedicamentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/backoffice/medicament')]
class MedicamentController extends AbstractController
{
    #[Route('/', name: 'app_medicament_index', methods: ['GET'])]
    public function index(Request $request, MedicamentRepository $medicamentRepository): Response
    {
        // Récupérer les paramètres
        $search = $request->query->get('search');
        $sortBy = $request->query->get('sortBy', 'dateCreation');
        $order = $request->query->get('order', 'DESC');
        $minStock = $request->query->get('minStock');
        $maxStock = $request->query->get('maxStock');

        // Convertir les valeurs numériques
        $minStock = $minStock !== null && $minStock !== '' ? (int)$minStock : null;
        $maxStock = $maxStock !== null && $maxStock !== '' ? (int)$maxStock : null;

        // Récupérer les médicaments filtrés
        $medicaments = $medicamentRepository->findAllWithSearch($search, $sortBy, $order, $minStock, $maxStock);

        // Statistiques
        $stats = $medicamentRepository->getStockStatistics();

        return $this->render('backoffice/medicament/index.html.twig', [
            'medicaments' => $medicaments,
            'search' => $search,
            'sortBy' => $sortBy,
            'order' => $order,
            'minStock' => $minStock,
            'maxStock' => $maxStock,
            'stats' => $stats,
        ]);
    }

    #[Route('/new', name: 'app_medicament_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $medicament = new Medicament();
        $form = $this->createForm(MedicamentType::class, $medicament);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($medicament);
            $entityManager->flush();

            $this->addFlash('success', 'Médicament créé avec succès.');
            return $this->redirectToRoute('app_medicament_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('backoffice/medicament/new.html.twig', [
            'medicament' => $medicament,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_medicament_show', methods: ['GET'])]
    public function show(Medicament $medicament): Response
    {
        return $this->render('backoffice/medicament/show.html.twig', [
            'medicament' => $medicament,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_medicament_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Medicament $medicament, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MedicamentType::class, $medicament);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Médicament modifié avec succès.');
            return $this->redirectToRoute('app_medicament_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('backoffice/medicament/edit.html.twig', [
            'medicament' => $medicament,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_medicament_delete', methods: ['POST'])]
    public function delete(Request $request, Medicament $medicament, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$medicament->getId(), $request->request->get('_token'))) {
            $entityManager->remove($medicament);
            $entityManager->flush();
            $this->addFlash('success', 'Médicament supprimé avec succès.');
        }

        return $this->redirectToRoute('app_medicament_index', [], Response::HTTP_SEE_OTHER);
    }
}