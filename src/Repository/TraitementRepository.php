<?php
<<<<<<< HEAD
// src/Repository/TraitementRepository.php

=======
>>>>>>> 343012190ba309c39188b230b31908e912145e26
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

<<<<<<< HEAD
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
=======
    // AJOUTER CETTE MÉTHODE
    public function findWithFilters(
        string $sortBy = 'id',
        string $order = 'ASC',
        string $statutFilter = 'all',
        string $search = ''
    ): array
    {
        // Création du QueryBuilder
        $qb = $this->createQueryBuilder('t');

        // 1. FILTRE PAR STATUT
        if ($statutFilter !== 'all') {
            $qb->andWhere('t.statut = :statut')
                ->setParameter('statut', $statutFilter);
        }

        // 2. FILTRE PAR RECHERCHE (nom ou description)
        if (!empty($search)) {
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('t.nom', ':search'),
                    $qb->expr()->like('t.description', ':search')
                )
            )
                ->setParameter('search', '%' . $search . '%');
        }

        // 3. TRI
        // Vérification pour éviter les injections SQL
        $allowedSortFields = ['id', 'nom', 'statut', 'dateCreation'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'id';
        }

        $qb->orderBy('t.' . $sortBy, $order);

        // Exécution de la requête
        return $qb->getQuery()->getResult();
>>>>>>> 343012190ba309c39188b230b31908e912145e26
    }
}