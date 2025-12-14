<?php

namespace App\Repository;

use App\Entity\Traitement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TraitementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Traitement::class);
    }

    /**
     * Récupérer tous les traitements avec recherche, tri et filtre
     */
    public function findAllWithSearch(
        ?string $search,
        string $sortBy = 'dateCreation',
        string $order = 'DESC',
        ?string $statut = null
    ): array {
        $qb = $this->createQueryBuilder('t');

        // 🔍 Recherche
        if ($search) {
            $qb->andWhere('t.nom LIKE :search OR t.description LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        // 📌 Filtre par statut
        if ($statut) {
            $qb->andWhere('t.statut = :statut')
                ->setParameter('statut', $statut);
        }

        // 🔃 Sécuriser le tri (important)
        $allowedSortFields = ['dateCreation', 'nom', 'statut', 'id'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'dateCreation';
        }

        $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

        $qb->orderBy('t.' . $sortBy, $order);

        return $qb->getQuery()->getResult();
    }

    /**
     * Statistiques par statut
     */
    public function countByStatut(): array
    {
        return $this->createQueryBuilder('t')
            ->select('t.statut, COUNT(t.id) as total')
            ->groupBy('t.statut')
            ->getQuery()
            ->getResult();
    }
}
