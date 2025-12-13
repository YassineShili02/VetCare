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
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/backoffice/medicament')]
class MedicamentController extends AbstractController
{
<<<<<<< HEAD
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
=======
    private function checkVeterinaryAccess(SessionInterface $session): void
    {
        if (!$session->get('veterinary_logged_in')) {
            $this->addFlash('error', 'Accès réservé aux vétérinaires. Veuillez vous connecter.');
            throw $this->createAccessDeniedException('Accès non autorisé');
        }
    }

    #[Route('/', name: 'app_medicament_index', methods: ['GET'])]
<<<<<<< HEAD
    public function index(MedicamentRepository $medicamentRepository, SessionInterface $session): Response
    {
        $this->checkVeterinaryAccess($session);

        return $this->render('backoffice/medicament/index.html.twig', [
            'medicaments' => $medicamentRepository->findAll(),
=======
    public function index(
        MedicamentRepository $medicamentRepository,
        SessionInterface $session,
        Request $request  // AJOUTER CE PARAMÈTRE
    ): Response
    {
        $this->checkVeterinaryAccess($session);

        // RÉCUPÉRER LES PARAMÈTRES DE L'URL
        $sortBy = $request->query->get('sort', 'id');
        $order = $request->query->get('order', 'ASC');
        $stockFilter = $request->query->get('stock', 'all');
        $searchTerm = $request->query->get('search', '');

        // UTILISER LA NOUVELLE MÉTHODE AVEC FILTRES
        $medicaments = $medicamentRepository->findWithFilters($sortBy, $order, $stockFilter, $searchTerm);

        return $this->render('backoffice/medicament/index.html.twig', [
            'medicaments' => $medicaments,
            // AJOUTER CES VARIABLES POUR LA TEMPLATE
            'currentSort' => $sortBy,
            'currentOrder' => $order,
            'currentStock' => $stockFilter,
            'currentSearch' => $searchTerm,
            'stockOptions' => [
                'all' => 'Tous les stocks',
                'low' => 'Stock faible (< 5)',
                'medium' => 'Stock moyen (5-20)',
                'high' => 'Stock élevé (> 20)'
            ]
>>>>>>> 3e2030c75b9f4a89f35ca6db7ad15e627d1d3c9e
>>>>>>> 343012190ba309c39188b230b31908e912145e26
        ]);
    }

    #[Route('/new', name: 'app_medicament_new', methods: ['GET', 'POST'])]
<<<<<<< HEAD
    public function new(Request $request, EntityManagerInterface $entityManager): Response
=======
    public function new(Request $request, EntityManagerInterface $entityManager, SessionInterface $session): Response
>>>>>>> 343012190ba309c39188b230b31908e912145e26
    {
        $this->checkVeterinaryAccess($session);

        $medicament = new Medicament();
        $form = $this->createForm(MedicamentType::class, $medicament);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($medicament);
            $entityManager->flush();

            $this->addFlash('success', 'Médicament créé avec succès.');
<<<<<<< HEAD
=======

>>>>>>> 343012190ba309c39188b230b31908e912145e26
            return $this->redirectToRoute('app_medicament_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('backoffice/medicament/new.html.twig', [
            'medicament' => $medicament,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_medicament_show', methods: ['GET'])]
<<<<<<< HEAD
    public function show(Medicament $medicament): Response
    {
        return $this->render('backoffice/medicament/show.html.twig', [
            'medicament' => $medicament,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_medicament_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Medicament $medicament, EntityManagerInterface $entityManager): Response
=======
    public function show(Medicament $medicament, SessionInterface $session): Response
>>>>>>> 343012190ba309c39188b230b31908e912145e26
    {
        $this->checkVeterinaryAccess($session);

        return $this->render('backoffice/medicament/show.html.twig', [
            'medicament' => $medicament,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_medicament_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Medicament $medicament, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        $this->checkVeterinaryAccess($session);

        $form = $this->createForm(MedicamentType::class, $medicament);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Médicament modifié avec succès.');
<<<<<<< HEAD
=======

>>>>>>> 343012190ba309c39188b230b31908e912145e26
            return $this->redirectToRoute('app_medicament_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('backoffice/medicament/edit.html.twig', [
            'medicament' => $medicament,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_medicament_delete', methods: ['POST'])]
<<<<<<< HEAD
    public function delete(Request $request, Medicament $medicament, EntityManagerInterface $entityManager): Response
=======
    public function delete(Request $request, Medicament $medicament, EntityManagerInterface $entityManager, SessionInterface $session): Response
>>>>>>> 343012190ba309c39188b230b31908e912145e26
    {
        $this->checkVeterinaryAccess($session);

        if ($this->isCsrfTokenValid('delete'.$medicament->getId(), $request->request->get('_token'))) {
            $entityManager->remove($medicament);
            $entityManager->flush();
<<<<<<< HEAD
=======

>>>>>>> 343012190ba309c39188b230b31908e912145e26
            $this->addFlash('success', 'Médicament supprimé avec succès.');
        }

        return $this->redirectToRoute('app_medicament_index', [], Response::HTTP_SEE_OTHER);
<<<<<<< HEAD
=======
    }

    #[Route('/{id}/update-statut', name: 'app_medicament_update_statut', methods: ['POST'])]
    public function updateStatut(Request $request, Medicament $medicament, EntityManagerInterface $entityManager): Response
    {
        // Pas de vérification d'authentification - accessible au client
        $nouveauStatut = $request->request->get('statut');

        if (in_array($nouveauStatut, ['accepted', 'refused', 'pending'])) {
            $medicament->setStatut($nouveauStatut);
            $entityManager->flush();

            $this->addFlash('success', 'Statut du médicament mis à jour.');
        } else {
            $this->addFlash('error', 'Statut invalide.');
        }

        return $this->redirectToRoute('app_client_interface');
    }

    #[Route('/frontoffice/medicaments', name: 'app_frontoffice_medicaments')]
    public function frontofficeMedicaments(MedicamentRepository $medicamentRepository): Response
    {
        // Pas de vérification d'authentification - accessible au client
        return $this->render('frontoffice/medicament_list.html.twig', [
            'medicaments' => $medicamentRepository->findAll(),
        ]);
>>>>>>> 343012190ba309c39188b230b31908e912145e26
    }
}