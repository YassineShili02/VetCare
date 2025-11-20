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
    public function index(TraitementRepository $traitementRepository, SessionInterface $session): Response
    {
        $this->checkVeterinaryAccess($session);

        return $this->render('backoffice/traitement/index.html.twig', [
            'traitements' => $traitementRepository->findAll(),
        ]);
    }

    // AJOUTER CES ROUTES MANQUANTES
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
        // Pas de vérification d'authentification - accessible au client
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
}