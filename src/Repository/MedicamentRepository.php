<?php
// src/Repository/MedicamentRepository.php

namespace App\Repository;

use App\Entity\Medicament;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MedicamentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Medicament::class);
    }

    public function findAllWithSearch(?string $search, ?string $sortBy, ?string $order, ?int $minStock, ?int $maxStock): array
    {
        $qb = $this->createQueryBuilder('m');

        if ($search) {
            $qb->andWhere('m.nom LIKE :search OR m.description LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($minStock !== null) {
            $qb->andWhere('m.stock >= :minStock')
                ->setParameter('minStock', $minStock);
        }

        if ($maxStock !== null) {
            $qb->andWhere('m.stock <= :maxStock')
                ->setParameter('maxStock', $maxStock);
        }

        if ($sortBy) {
            $qb->orderBy('m.' . $sortBy, $order === 'ASC' ? 'ASC' : 'DESC');
        } else {
            $qb->orderBy('m.dateCreation', 'DESC');
        }

        return $qb->getQuery()->getResult();
    }

    public function getStockStatistics(): array
    {
        $result = $this->createQueryBuilder('m')
            ->select('MIN(m.stock) as minStock', 'MAX(m.stock) as maxStock', 'AVG(m.stock) as avgStock', 'SUM(m.stock) as totalStock', 'COUNT(m.id) as total')
            ->getQuery()
            ->getSingleResult();

        return [
            'minStock' => $result['minStock'] ?? 0,
            'maxStock' => $result['maxStock'] ?? 0,
            'avgStock' => $result['avgStock'] ? round($result['avgStock'], 2) : 0,
            'totalStock' => $result['totalStock'] ?? 0,
            'total' => $result['total'] ?? 0,
        ];
    }

    public function save(Medicament $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Medicament $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}