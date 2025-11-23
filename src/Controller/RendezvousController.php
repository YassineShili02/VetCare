<?php

namespace App\Controller;

use App\Entity\Rendezvous;
use App\Entity\Veterinaire;
use App\Form\RendezvousType;
use App\Service\TimeSlotService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RendezvousController extends AbstractController
{
    /**
     * Page principale: choisir un vétérinaire (optionnel) et voir les créneaux disponibles
     */
    #[Route('/rendezvous/new', name: 'rendezvous_new')]
    public function new(Request $request, EntityManagerInterface $em, TimeSlotService $timeSlotService): Response
    {
        $selectedVetId = $request->query->get('vet');
        $selectedVet = null;
        
        if ($selectedVetId) {
            $selectedVet = $em->getRepository(Veterinaire::class)->find($selectedVetId);
        }
        
        // Récupérer les créneaux disponibles pour les 7 prochains jours
        $availableSlots = $timeSlotService->getUpcomingAvailableSlots(7, $selectedVet);
        
        // Liste des vétérinaires pour le dropdown
        $veterinaires = $em->getRepository(Veterinaire::class)->findBy(['actif' => true]);
        
        return $this->render('rendezvous/new.html.twig', [
            'veterinaires' => $veterinaires,
            'selectedVet' => $selectedVet,
            'availableSlots' => $availableSlots,
        ]);
    }

    /**
     * Créer un rendez-vous pour un créneau spécifique
     */
    #[Route('/rendezvous/book', name: 'rendezvous_book', methods: ['GET', 'POST'])]
    public function book(Request $request, EntityManagerInterface $em): Response
    {
        $rendezvous = new Rendezvous();
        
        // Pré-remplir avec les données du créneau sélectionné
        $datetime = $request->query->get('datetime');
        $vetId = $request->query->get('vet');
        
        if ($datetime) {
            $rendezvous->setDateHeure(new \DateTime($datetime));
        }
        
        if ($vetId) {
            $vet = $em->getRepository(Veterinaire::class)->find($vetId);
            $rendezvous->setVeterinaire($vet);
        }
        
        // Définir le statut par défaut
        $rendezvous->setStatut('en_attente');
        $rendezvous->setStatutPaiement('non_paye');
        
        $form = $this->createForm(RendezvousType::class, $rendezvous, [
            'is_admin' => false // Client view
        ]);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($rendezvous);
            $em->flush();
            
            $this->addFlash('success', 'Votre demande de rendez-vous a été envoyée ! Vous serez notifié une fois confirmé.');
            return $this->redirectToRoute('rendezvous_confirmation', ['id' => $rendezvous->getId()]);
        }
        
        return $this->render('rendezvous/book.html.twig', [
            'form' => $form->createView(),
            'rendezvous' => $rendezvous,
        ]);
    }

    /**
     * Page de confirmation après réservation
     */
    #[Route('/rendezvous/confirmation/{id}', name: 'rendezvous_confirmation')]
    public function confirmation(Rendezvous $rendezvous): Response
    {
        return $this->render('rendezvous/confirmation.html.twig', [
            'rendezvous' => $rendezvous,
        ]);
    }

    /**
     * Liste des rendez-vous (Vue Admin/Vétérinaire)
     */
    #[Route('/admin/rendezvous/list', name: 'rendezvous_list')]
    public function list(EntityManagerInterface $em, Request $request): Response
    {
        $statut = $request->query->get('statut');
        $vetId = $request->query->get('vet');
        
        $qb = $em->getRepository(Rendezvous::class)->createQueryBuilder('r');
        
        if ($statut) {
            $qb->andWhere('r.statut = :statut')
               ->setParameter('statut', $statut);
        }
        
        if ($vetId) {
            $qb->andWhere('r.veterinaire = :vet')
               ->setParameter('vet', $vetId);
        }
        
        $qb->orderBy('r.dateHeure', 'DESC');
        
        $rendezvous = $qb->getQuery()->getResult();
        
        // Pour les filtres
        $veterinaires = $em->getRepository(Veterinaire::class)->findBy(['actif' => true]);
        
        return $this->render('rendezvous/list.html.twig', [
            'rendezvous' => $rendezvous,
            'veterinaires' => $veterinaires,
            'currentStatut' => $statut,
            'currentVet' => $vetId,
        ]);
    }

    /**
     * Éditer un rendez-vous (Admin/Vet view avec tous les champs)
     */
    #[Route('/admin/rendezvous/edit/{id}', name: 'rendezvous_edit')]
    public function edit(Rendezvous $rendezvous, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(RendezvousType::class, $rendezvous, [
            'is_admin' => true // Admin view avec tous les champs
        ]);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Rendez-vous mis à jour !');
            return $this->redirectToRoute('rendezvous_list');
        }
        
        return $this->render('rendezvous/edit.html.twig', [
            'form' => $form->createView(),
            'rendezvous' => $rendezvous,
        ]);
    }

    /**
     * Approuver un rendez-vous (vétérinaire)
     */
    #[Route('/admin/rendezvous/approve/{id}', name: 'rendezvous_approve')]
    public function approve(Rendezvous $rendezvous, EntityManagerInterface $em): Response
    {
        $rendezvous->setStatut('confirme');
        $em->flush();
        
        $this->addFlash('success', 'Rendez-vous confirmé !');
        return $this->redirectToRoute('rendezvous_list');
    }

    /**
     * Refuser un rendez-vous (vétérinaire)
     */
    #[Route('/admin/rendezvous/reject/{id}', name: 'rendezvous_reject')]
    public function reject(Rendezvous $rendezvous, EntityManagerInterface $em): Response
    {
        $rendezvous->setStatut('refuse');
        $em->flush();
        
        $this->addFlash('warning', 'Rendez-vous refusé.');
        return $this->redirectToRoute('rendezvous_list');
    }

    /**
     * Supprimer un rendez-vous
     */
    #[Route('/admin/rendezvous/delete/{id}', name: 'rendezvous_delete', methods: ['POST'])]
    public function delete(Rendezvous $rendezvous, EntityManagerInterface $em): Response
    {
        $em->remove($rendezvous);
        $em->flush();
        
        $this->addFlash('success', 'Rendez-vous supprimé !');
        return $this->redirectToRoute('rendezvous_list');
    }
}