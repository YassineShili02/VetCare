<?php
// src/Repository/TraitementRepository.php

namespace App\Repository;

use App\Entity\Traitement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Traitement>
 */
class TraitementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Traitement::class);
    }

    public function findAllWithSearch(?string $search, ?string $sortBy, ?string $order, ?string $statut)
    {
        $queryBuilder = $this->createQueryBuilder('t');

        if ($search) {
            $queryBuilder
                ->andWhere('t.nom LIKE :search OR t.description LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($statut) {
            $queryBuilder
                ->andWhere('t.statut = :statut')
                ->setParameter('statut', $statut);
        }

        // Tri
        if ($sortBy) {
            $queryBuilder->orderBy('t.' . $sortBy, $order ?? 'ASC');
        } else {
            $queryBuilder->orderBy('t.dateCreation', 'DESC');
        }

        return $queryBuilder->getQuery()->getResult();
    }

    public function countByStatut()
    {
        return $this->createQueryBuilder('t')
            ->select('t.statut, COUNT(t.id) as count')
            ->groupBy('t.statut')
            ->getQuery()
            ->getResult();
    }
}