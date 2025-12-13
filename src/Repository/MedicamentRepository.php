<?php
// src/Repository/MedicamentRepository.php

namespace App\Repository;

use App\Entity\Medicament;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Medicament>
 */
class MedicamentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Medicament::class);
    }

    public function findAllWithSearch(?string $search, ?string $sortBy, ?string $order, ?int $minStock, ?int $maxStock)
    {
        $queryBuilder = $this->createQueryBuilder('m');

        if ($search) {
            $queryBuilder
                ->andWhere('m.nom LIKE :search OR m.description LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($minStock !== null) {
            $queryBuilder
                ->andWhere('m.stock >= :minStock')
                ->setParameter('minStock', $minStock);
        }

        if ($maxStock !== null) {
            $queryBuilder
                ->andWhere('m.stock <= :maxStock')
                ->setParameter('maxStock', $maxStock);
        }

        // Tri
        if ($sortBy) {
            $queryBuilder->orderBy('m.' . $sortBy, $order ?? 'ASC');
        } else {
            $queryBuilder->orderBy('m.dateCreation', 'DESC');
        }

        return $queryBuilder->getQuery()->getResult();
    }

    public function getStockStatistics()
    {
        return $this->createQueryBuilder('m')
            ->select('MIN(m.stock) as minStock', 'MAX(m.stock) as maxStock', 'AVG(m.stock) as avgStock')
            ->getQuery()
            ->getSingleResult();
    }
}