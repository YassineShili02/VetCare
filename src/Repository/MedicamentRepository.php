<?php
<<<<<<< HEAD
// src/Repository/MedicamentRepository.php

=======
>>>>>>> 343012190ba309c39188b230b31908e912145e26
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

<<<<<<< HEAD
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
=======
    // AJOUTER CETTE MÉTHODE
    public function findWithFilters(
        string $sortBy = 'id',
        string $order = 'ASC',
        string $stockFilter = 'all',
        string $search = ''
    ): array
    {
        // Création du QueryBuilder
        $qb = $this->createQueryBuilder('m');

        // 1. FILTRE PAR NIVEAU DE STOCK
        if ($stockFilter !== 'all') {
            switch ($stockFilter) {
                case 'low':
                    $qb->andWhere('m.stock < 5');
                    break;
                case 'medium':
                    $qb->andWhere('m.stock >= 5 AND m.stock <= 20');
                    break;
                case 'high':
                    $qb->andWhere('m.stock > 20');
                    break;
            }
        }

        // 2. FILTRE PAR RECHERCHE (nom ou description)
        if (!empty($search)) {
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('m.nom', ':search'),
                    $qb->expr()->like('m.description', ':search')
                )
            )
                ->setParameter('search', '%' . $search . '%');
        }

        // 3. TRI
        // Vérification pour éviter les injections SQL
        $allowedSortFields = ['id', 'nom', 'stock', 'dateCreation'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'id';
        }

        $qb->orderBy('m.' . $sortBy, $order);

        // Exécution de la requête
        return $qb->getQuery()->getResult();
>>>>>>> 343012190ba309c39188b230b31908e912145e26
    }
}