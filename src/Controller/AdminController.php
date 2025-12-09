<?php

namespace App\Controller;

use App\Entity\Clinique;
use App\Entity\Rendezvous;
use App\Entity\Veterinaire;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    /**
     * Redirection de /admin vers /admin/dashboard
     */
    #[Route('', name: 'admin_index')]
    public function index(): Response
    {
        return $this->redirectToRoute('admin_dashboard');
    }

    /**
     * Dashboard principal de l'administration
     */
    #[Route('/dashboard', name: 'admin_dashboard')]
    public function dashboard(EntityManagerInterface $em): Response
    {
        // Date du jour (début et fin)
        $aujourdHui = new \DateTime('today');
        $demain = new \DateTime('tomorrow');

        // Statistiques
        $stats = [
            'total_rdv' => $em->getRepository(Rendezvous::class)->count([]),
            'en_attente' => $em->getRepository(Rendezvous::class)->count(['statut' => 'en_attente']),
            'confirmes' => $em->getRepository(Rendezvous::class)->count(['statut' => 'confirme']),
            'cliniques' => $em->getRepository(Clinique::class)->count([]),
            'cliniques_actives' => $em->getRepository(Clinique::class)->count(['actif' => true]),
            'rdv_today' => $em->getRepository(Rendezvous::class)
                ->createQueryBuilder('r')
                ->select('COUNT(r.id)')
                ->where('r.dateHeure >= :aujourdHui')
                ->andWhere('r.dateHeure < :demain')
                ->setParameter('aujourdHui', $aujourdHui)
                ->setParameter('demain', $demain)
                ->getQuery()
                ->getSingleScalarResult(),
        ];

        // Derniers rendez-vous
        $derniers_rdv = $em->getRepository(Rendezvous::class)
            ->createQueryBuilder('r')
            ->orderBy('r.dateHeure', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        // Vétérinaires actifs
        $veterinaires = $em->getRepository(Veterinaire::class)
            ->findBy(['actif' => true], ['nom' => 'ASC'], 10);

        return $this->render('admin/dashboard.html.twig', [
            'stats' => $stats,
            'derniers_rdv' => $derniers_rdv,
            'veterinaires' => $veterinaires,
        ]);
    }

    /**
     * Liste des cliniques
     */
    #[Route('/cliniques', name: 'clinique_index')]
    public function cliniques(EntityManagerInterface $em): Response
    {
        $cliniques = $em->getRepository(Clinique::class)->findAll();

        return $this->render('admin/clinique/index.html.twig', [
            'cliniques' => $cliniques,
        ]);
    }
}