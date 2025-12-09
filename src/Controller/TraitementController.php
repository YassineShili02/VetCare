<?php

namespace App\Controller;

use App\Entity\Traitement;
use App\Form\TraitementType;
use App\Repository\TraitementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/backoffice/traitement')]
class TraitementController extends AbstractController
{
    private function checkVeterinaryAccess(SessionInterface $session): void
    {
        if (!$session->get('veterinary_logged_in')) {
            $this->addFlash('error', 'Accès réservé aux vétérinaires. Veuillez vous connecter.');
            throw $this->createAccessDeniedException('Accès non autorisé');
        }
    }

    #[Route('/', name: 'app_traitement_index', methods: ['GET'])]
<<<<<<< HEAD
    public function index(TraitementRepository $traitementRepository, SessionInterface $session): Response
    {
        $this->checkVeterinaryAccess($session);

        return $this->render('backoffice/traitement/index.html.twig', [
            'traitements' => $traitementRepository->findAll(),
        ]);
    }

    // AJOUTER CES ROUTES MANQUANTES
=======
    public function index(TraitementRepository $traitementRepository, SessionInterface $session, Request $request): Response
    {
        $this->checkVeterinaryAccess($session);

        $sortBy = $request->query->get('sort', 'id');
        $order = $request->query->get('order', 'ASC');
        $statutFilter = $request->query->get('statut', 'all');
        $searchTerm = $request->query->get('search', '');

        $traitements = $traitementRepository->findWithFilters($sortBy, $order, $statutFilter, $searchTerm);

        return $this->render('backoffice/traitement/index.html.twig', [
            'traitements' => $traitements,
            'currentSort' => $sortBy,
            'currentOrder' => $order,
            'currentStatut' => $statutFilter,
            'currentSearch' => $searchTerm,
            'statuts' => [
                'all' => 'Tous les statuts',
                'pending' => 'En attente',
                'in_progress' => 'En cours',
                'completed' => 'Terminé',
                'cancelled' => 'Annulé',
                'accepted' => 'Accepté',
                'refused' => 'Refusé'
            ]
        ]);
    }

>>>>>>> 3e2030c75b9f4a89f35ca6db7ad15e627d1d3c9e
    #[Route('/new', name: 'app_traitement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        $this->checkVeterinaryAccess($session);

        $traitement = new Traitement();
        $form = $this->createForm(TraitementType::class, $traitement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($traitement);
            $entityManager->flush();

            $this->addFlash('success', 'Traitement créé avec succès.');
            return $this->redirectToRoute('app_traitement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('backoffice/traitement/new.html.twig', [
            'traitement' => $traitement,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_traitement_show', methods: ['GET'])]
    public function show(Traitement $traitement, SessionInterface $session): Response
    {
        $this->checkVeterinaryAccess($session);

        return $this->render('backoffice/traitement/show.html.twig', [
            'traitement' => $traitement,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_traitement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Traitement $traitement, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        $this->checkVeterinaryAccess($session);

        $form = $this->createForm(TraitementType::class, $traitement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
<<<<<<< HEAD

=======
>>>>>>> 3e2030c75b9f4a89f35ca6db7ad15e627d1d3c9e
            $this->addFlash('success', 'Traitement modifié avec succès.');
            return $this->redirectToRoute('app_traitement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('backoffice/traitement/edit.html.twig', [
            'traitement' => $traitement,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_traitement_delete', methods: ['POST'])]
    public function delete(Request $request, Traitement $traitement, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        $this->checkVeterinaryAccess($session);

        if ($this->isCsrfTokenValid('delete'.$traitement->getId(), $request->request->get('_token'))) {
            $entityManager->remove($traitement);
            $entityManager->flush();
            $this->addFlash('success', 'Traitement supprimé avec succès.');
        }

        return $this->redirectToRoute('app_traitement_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/update-statut', name: 'app_traitement_update_statut', methods: ['POST'])]
    public function updateStatut(Request $request, Traitement $traitement, EntityManagerInterface $entityManager): Response
    {
<<<<<<< HEAD
        // Pas de vérification d'authentification - accessible au client
=======
>>>>>>> 3e2030c75b9f4a89f35ca6db7ad15e627d1d3c9e
        $nouveauStatut = $request->request->get('statut');

        if (in_array($nouveauStatut, ['pending', 'in_progress', 'completed', 'cancelled', 'accepted', 'refused'])) {
            $traitement->setStatut($nouveauStatut);
            $entityManager->flush();
            $this->addFlash('success', 'Statut du traitement mis à jour.');
        } else {
            $this->addFlash('error', 'Statut invalide.');
        }

        return $this->redirectToRoute('app_client_interface');
    }

    #[Route('/frontoffice/traitements', name: 'app_frontoffice_traitements')]
    public function frontofficeTraitements(TraitementRepository $traitementRepository): Response
    {
        return $this->render('frontoffice/traitement_list.html.twig', [
            'traitements' => $traitementRepository->findAll(),
        ]);
    }
<<<<<<< HEAD
    
}
=======
}
>>>>>>> 3e2030c75b9f4a89f35ca6db7ad15e627d1d3c9e
