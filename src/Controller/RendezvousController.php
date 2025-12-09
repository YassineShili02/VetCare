<?php

namespace App\Controller;

use App\Entity\Clinique;
use App\Entity\Rendezvous;
use App\Entity\User;
use App\Form\RendezvousType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/rendezvous')]
final class RendezvousController extends AbstractController
{
    /**
     * Page principale : liste des cliniques disponibles
     */
    #[Route('/new', name: 'rendezvous_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $selectedCliniqueId = $request->query->get('clinique');
        $selectedClinique = null;
        
        if ($selectedCliniqueId) {
            $selectedClinique = $em->getRepository(Clinique::class)->find($selectedCliniqueId);
        }
        
        // Liste des cliniques actives
        $cliniques = $em->getRepository(Clinique::class)->findBy(['actif' => true], ['nom' => 'ASC']);
        
        // Statistiques pour chaque clinique
        $cliniquesData = [];
        foreach ($cliniques as $clinique) {
            $cliniquesData[] = [
                'clinique' => $clinique,
                'nombreRendezvous' => $clinique->getNombreRendezvous(),
                'isOuvertAujourdhui' => $clinique->isOuvertAujourdhui(),
                'horairesAujourdhui' => $clinique->getHorairesAujourdhui(),
            ];
        }
        
        return $this->render('rendezvous/new.html.twig', [
            'cliniquesData' => $cliniquesData,
            'selectedClinique' => $selectedClinique,
        ]);
    }

    /**
     * Créer un nouveau rendez-vous
     */
    #[Route('/book', name: 'rendezvous_book', methods: ['GET', 'POST'])]
    public function book(Request $request, EntityManagerInterface $em): Response
    {
        $rendezvous = new Rendezvous();
        $user = $this->getUser();
        
        // Pré-remplir avec les données de l'utilisateur connecté
        if ($user instanceof User) {
            $rendezvous->setClient($user);
            $rendezvous->setNomClient($user->getFullName());
            $rendezvous->setEmailClient($user->getEmail());
            $rendezvous->setTelephoneClient($user->getPhone());
        }
        
        // Pré-sélectionner la clinique si passée en paramètre
        $cliniqueId = $request->query->get('clinique');
        if ($cliniqueId) {
            $clinique = $em->getRepository(Clinique::class)->find($cliniqueId);
            if ($clinique) {
                $rendezvous->setClinique($clinique);
            }
        }
        
        // Pré-remplir la date si passée en paramètre
        $datetime = $request->query->get('datetime');
        if ($datetime) {
            try {
                $rendezvous->setDateHeure(new \DateTime($datetime));
            } catch (\Exception $e) {
                $this->addFlash('warning', 'Format de date invalide');
            }
        }
        
        $form = $this->createForm(RendezvousType::class, $rendezvous, [
            'is_admin' => false,
            'show_animal_select' => $user !== null
        ]);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifier que la date est dans le futur
            if ($rendezvous->getDateHeure() <= new \DateTime()) {
                $this->addFlash('error', 'La date du rendez-vous doit être dans le futur');
                return $this->render('rendezvous/book.html.twig', [
                    'form' => $form->createView(),
                    'rendezvous' => $rendezvous,
                ]);
            }
            
            // Vérifier si le créneau n'est pas déjà pris
            $existingRdv = $em->getRepository(Rendezvous::class)->createQueryBuilder('r')
                ->where('r.dateHeure = :date')
                ->andWhere('r.clinique = :clinique')
                ->andWhere('r.statut IN (:statuts)')
                ->setParameter('date', $rendezvous->getDateHeure())
                ->setParameter('clinique', $rendezvous->getClinique())
                ->setParameter('statuts', ['en_attente', 'confirme'])
                ->getQuery()
                ->getOneOrNullResult();
            
            if ($existingRdv) {
                $this->addFlash('error', 'Ce créneau horaire n\'est plus disponible. Veuillez en choisir un autre.');
                return $this->render('rendezvous/book.html.twig', [
                    'form' => $form->createView(),
                    'rendezvous' => $rendezvous,
                ]);
            }
            
            $em->persist($rendezvous);
            $em->flush();
            
            $this->addFlash('success', 'Votre demande de rendez-vous a été envoyée avec succès ! Vous serez notifié une fois confirmé.');
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
    #[Route('/confirmation/{id}', name: 'rendezvous_confirmation')]
    public function confirmation(Rendezvous $rendezvous): Response
    {
        // Vérifier que l'utilisateur connecté est bien le propriétaire
        $user = $this->getUser();
        if ($user && $rendezvous->getClient() !== $user && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à ce rendez-vous');
        }
        
        return $this->render('rendezvous/confirmation.html.twig', [
            'rendezvous' => $rendezvous,
        ]);
    }

    /**
     * Mes rendez-vous (utilisateur connecté)
     */
    #[Route('/mes-rendezvous', name: 'rendezvous_mes_rendezvous')]
    #[IsGranted('ROLE_USER')]
    public function mesRendezvous(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        
        $rendezvous = $em->getRepository(Rendezvous::class)
            ->createQueryBuilder('r')
            ->where('r.client = :user')
            ->setParameter('user', $user)
            ->orderBy('r.dateHeure', 'DESC')
            ->getQuery()
            ->getResult();
        
        return $this->render('rendezvous/mes_rendezvous.html.twig', [
            'rendezvous' => $rendezvous,
        ]);
    }
    

    /**
     * Annuler un rendez-vous (utilisateur)
     */
    #[Route('/annuler/{id}', name: 'rendezvous_annuler', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function annuler(Rendezvous $rendezvous, EntityManagerInterface $em, Request $request): Response
    {
        // Vérifier que c'est bien le rendez-vous de l'utilisateur
        if ($rendezvous->getClient() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }
        
        // Vérifier le token CSRF
        if (!$this->isCsrfTokenValid('annuler' . $rendezvous->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token de sécurité invalide');
            return $this->redirectToRoute('rendezvous_mes_rendezvous');
        }
        
        // On peut annuler uniquement si le rendez-vous est en attente ou confirmé
        if (!in_array($rendezvous->getStatut(), ['en_attente', 'confirme'])) {
            $this->addFlash('error', 'Ce rendez-vous ne peut plus être annulé');
            return $this->redirectToRoute('rendezvous_mes_rendezvous');
        }
        
        $rendezvous->setStatut('annule');
        $em->flush();
        
        $this->addFlash('success', 'Rendez-vous annulé avec succès');
        return $this->redirectToRoute('rendezvous_mes_rendezvous');
    }

    /**
     * Liste des rendez-vous (Vue Admin)
     */
    #[Route('/admin/list', name: 'rendezvous_admin_list')]
    #[IsGranted('ROLE_ADMIN')]
    public function adminList(EntityManagerInterface $em, Request $request): Response
    {
        $statut = $request->query->get('statut');
        $cliniqueId = $request->query->get('clinique');
        $date = $request->query->get('date');
        
        $qb = $em->getRepository(Rendezvous::class)->createQueryBuilder('r')
            ->leftJoin('r.clinique', 'c')
            ->leftJoin('r.client', 'u');
        
        if ($statut) {
            $qb->andWhere('r.statut = :statut')
               ->setParameter('statut', $statut);
        }
        
        if ($cliniqueId) {
            $qb->andWhere('r.clinique = :clinique')
               ->setParameter('clinique', $cliniqueId);
        }
        
        if ($date) {
            $qb->andWhere('DATE(r.dateHeure) = :date')
               ->setParameter('date', $date);
        }
        
        $qb->orderBy('r.dateHeure', 'DESC');
        
        $rendezvous = $qb->getQuery()->getResult();
        
        // Pour les filtres
        $cliniques = $em->getRepository(Clinique::class)->findBy(['actif' => true], ['nom' => 'ASC']);
        
        // Statistiques
        $stats = [
            'total' => count($rendezvous),
            'en_attente' => count(array_filter($rendezvous, fn($r) => $r->getStatut() === 'en_attente')),
            'confirme' => count(array_filter($rendezvous, fn($r) => $r->getStatut() === 'confirme')),
            'termine' => count(array_filter($rendezvous, fn($r) => $r->getStatut() === 'termine')),
            'annule' => count(array_filter($rendezvous, fn($r) => $r->getStatut() === 'annule')),
        ];
        
        return $this->render('rendezvous/admin_list.html.twig', [
            'rendezvous' => $rendezvous,
            'cliniques' => $cliniques,
            'stats' => $stats,
            'currentStatut' => $statut,
            'currentClinique' => $cliniqueId,
            'currentDate' => $date,
        ]);
    }

    /**
     * Éditer un rendez-vous (Admin)
     */
    #[Route('/admin/edit/{id}', name: 'rendezvous_admin_edit')]
    #[IsGranted('ROLE_ADMIN')]
    public function adminEdit(Rendezvous $rendezvous, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(RendezvousType::class, $rendezvous, [
            'is_admin' => true,
            'show_animal_select' => false
        ]);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Rendez-vous mis à jour avec succès');
            return $this->redirectToRoute('rendezvous_admin_list');
        }
        
        return $this->render('rendezvous/admin_edit.html.twig', [
            'form' => $form->createView(),
            'rendezvous' => $rendezvous,
        ]);
    }

    /**
     * Confirmer un rendez-vous (Admin)
     */
    #[Route('/admin/confirm/{id}', name: 'rendezvous_admin_confirm', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminConfirm(Rendezvous $rendezvous, EntityManagerInterface $em, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('confirm' . $rendezvous->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token de sécurité invalide');
            return $this->redirectToRoute('rendezvous_admin_list');
        }
        
        $rendezvous->setStatut('confirme');
        $em->flush();
        
        $this->addFlash('success', 'Rendez-vous confirmé avec succès');
        return $this->redirectToRoute('rendezvous_admin_list');
    }

    /**
     * Refuser un rendez-vous (Admin)
     */
    #[Route('/admin/reject/{id}', name: 'rendezvous_admin_reject', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminReject(Rendezvous $rendezvous, EntityManagerInterface $em, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('reject' . $rendezvous->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token de sécurité invalide');
            return $this->redirectToRoute('rendezvous_admin_list');
        }
        
        $rendezvous->setStatut('refuse');
        $em->flush();
        
        $this->addFlash('warning', 'Rendez-vous refusé');
        return $this->redirectToRoute('rendezvous_admin_list');
    }

    /**
     * Marquer un rendez-vous comme terminé (Admin)
     */
    #[Route('/admin/complete/{id}', name: 'rendezvous_admin_complete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminComplete(Rendezvous $rendezvous, EntityManagerInterface $em, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('complete' . $rendezvous->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token de sécurité invalide');
            return $this->redirectToRoute('rendezvous_admin_list');
        }
        
        $rendezvous->setStatut('termine');
        $em->flush();
        
        $this->addFlash('success', 'Rendez-vous marqué comme terminé');
        return $this->redirectToRoute('rendezvous_admin_list');
    }

    /**
     * Supprimer un rendez-vous (Admin)
     */
    #[Route('/admin/delete/{id}', name: 'rendezvous_admin_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminDelete(Rendezvous $rendezvous, EntityManagerInterface $em, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('delete' . $rendezvous->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token de sécurité invalide');
            return $this->redirectToRoute('rendezvous_admin_list');
        }
        
        $em->remove($rendezvous);
        $em->flush();
        
        $this->addFlash('success', 'Rendez-vous supprimé avec succès');
        return $this->redirectToRoute('rendezvous_admin_list');
    }

    /**
     * Calendrier des rendez-vous (Admin)
     */
    #[Route('/admin/calendar', name: 'rendezvous_admin_calendar')]
    #[IsGranted('ROLE_ADMIN')]
    public function adminCalendar(EntityManagerInterface $em, Request $request): Response
    {
        $cliniqueId = $request->query->get('clinique');
        $month = $request->query->get('month', date('Y-m'));
        
        $qb = $em->getRepository(Rendezvous::class)->createQueryBuilder('r')
            ->where('DATE_FORMAT(r.dateHeure, \'%Y-%m\') = :month')
            ->setParameter('month', $month)
            ->andWhere('r.statut IN (:statuts)')
            ->setParameter('statuts', ['en_attente', 'confirme', 'termine']);
        
        if ($cliniqueId) {
            $qb->andWhere('r.clinique = :clinique')
               ->setParameter('clinique', $cliniqueId);
        }
        
        $rendezvous = $qb->orderBy('r.dateHeure', 'ASC')->getQuery()->getResult();
        
        $cliniques = $em->getRepository(Clinique::class)->findBy(['actif' => true], ['nom' => 'ASC']);
        
        return $this->render('rendezvous/admin_calendar.html.twig', [
            'rendezvous' => $rendezvous,
            'cliniques' => $cliniques,
            'currentMonth' => $month,
            'currentClinique' => $cliniqueId,
        ]);
    }
}