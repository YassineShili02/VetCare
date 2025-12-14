<?php
// src/Controller/TraitementController.php

namespace App\Controller;

use App\Entity\Traitement;
use App\Form\TraitementType;
use App\Repository\TraitementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/backoffice/traitement')]
class TraitementController extends AbstractController
{
    #[Route('/', name: 'app_traitement_index', methods: ['GET'])]
    public function index(Request $request, TraitementRepository $traitementRepository): Response
    {
        $search = $request->query->get('search');
        $sortBy = $request->query->get('sortBy', 'dateCreation');
        $order = $request->query->get('order', 'DESC');
        $statut = $request->query->get('statut');

        $traitements = $traitementRepository->findAll();

        $statutStats = $traitementRepository->countByStatut();

        return $this->render('backoffice/traitement/index.html.twig', [
            'traitements' => $traitements,
            'search' => $search,
            'sortBy' => $sortBy,
            'order' => $order,
            'statut' => $statut,
            'statutStats' => $statutStats,
        ]);
    }

    #[Route('/new', name: 'app_traitement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
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
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_traitement_show', methods: ['GET'])]
    public function show(Traitement $traitement): Response
    {
        return $this->render('backoffice/traitement/show.html.twig', [
            'traitement' => $traitement,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_traitement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Traitement $traitement, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TraitementType::class, $traitement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Traitement modifié avec succès.');
            return $this->redirectToRoute('app_traitement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('backoffice/traitement/edit.html.twig', [
            'traitement' => $traitement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_traitement_delete', methods: ['POST'])]
    public function delete(Request $request, Traitement $traitement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$traitement->getId(), $request->request->get('_token'))) {
            $entityManager->remove($traitement);
            $entityManager->flush();
            $this->addFlash('success', 'Traitement supprimé avec succès.');
        }

        return $this->redirectToRoute('app_traitement_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/frontoffice/traitements', name: 'app_frontoffice_traitements_list', methods: ['GET'])]
    public function frontofficeTraitements(TraitementRepository $traitementRepository): Response
    {
        return $this->render('frontoffice/traitement_list.html.twig', [
            'traitements' => $traitementRepository->findAll(),
        ]);
    }
}