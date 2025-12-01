<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function save(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Recherche et tri des utilisateurs avec filtres
     */
    public function searchAndFilter(array $filters = [], array $sort = []): array
    {
        $qb = $this->createQueryBuilder('u');
        
        // Filtres
        if (!empty($filters['search'])) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->like('u.firstName', ':search'),
                $qb->expr()->like('u.lastName', ':search'),
                $qb->expr()->like('u.email', ':search'),
                $qb->expr()->like('u.phone', ':search')
            ))
            ->setParameter('search', '%' . $filters['search'] . '%');
        }
        
        if (!empty($filters['role'])) {
            $role = $filters['role'];
            if ($role === 'ROLE_USER') {
                $qb->andWhere('u.roles LIKE :role_user')
                   ->setParameter('role_user', '%"ROLE_USER"%')
                   ->andWhere('u.roles NOT LIKE :role_vet')
                   ->setParameter('role_vet', '%"ROLE_VET"%')
                   ->andWhere('u.roles NOT LIKE :role_admin')
                   ->setParameter('role_admin', '%"ROLE_ADMIN"%');
            } elseif ($role === 'ROLE_VET') {
                $qb->andWhere('u.roles LIKE :role')
                   ->setParameter('role', '%"ROLE_VET"%');
            } elseif ($role === 'ROLE_ADMIN') {
                $qb->andWhere('u.roles LIKE :role')
                   ->setParameter('role', '%"ROLE_ADMIN"%');
            }
        }
        
        if (!empty($filters['created_from'])) {
            $qb->andWhere('u.createdAt >= :created_from')
               ->setParameter('created_from', $filters['created_from']);
        }
        
        if (!empty($filters['created_to'])) {
            $qb->andWhere('u.createdAt <= :created_to')
               ->setParameter('created_to', $filters['created_to']);
        }

        // Tri
        $sortField = $sort['field'] ?? 'u.lastName';
        $sortDirection = $sort['direction'] ?? 'ASC';
        
        // Validation du champ de tri
        $allowedSortFields = ['u.id', 'u.email', 'u.firstName', 'u.lastName', 'u.phone', 'u.createdAt', 'u.updatedAt'];
        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'u.lastName';
        }
        
        $qb->orderBy($sortField, $sortDirection);

        return $qb->getQuery()->getResult();
    }

    /**
     * Récupère tous les utilisateurs triés par nom
     */
    public function findAllOrderedByName(): array
    {
        return $this->createQueryBuilder('u')
            ->orderBy('u.lastName', 'ASC')
            ->addOrderBy('u.firstName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche d'utilisateurs par terme (pour autocomplétion)
     */
    public function searchByTerm(string $term, int $limit = 10): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.firstName LIKE :term')
            ->orWhere('u.lastName LIKE :term')
            ->orWhere('u.email LIKE :term')
            ->setParameter('term', '%' . $term . '%')
            ->orderBy('u.lastName', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte les utilisateurs par rôle
     */
    public function countByRole(): array
    {
        $results = $this->createQueryBuilder('u')
            ->select('COUNT(u.id) as count')
            ->addSelect("
                CASE 
                    WHEN u.roles LIKE '%\"ROLE_ADMIN\"%' THEN 'Administrateur'
                    WHEN u.roles LIKE '%\"ROLE_VET\"%' THEN 'Vétérinaire'
                    ELSE 'Client'
                END as role_group
            ")
            ->groupBy('role_group')
            ->getQuery()
            ->getResult();

        $stats = [
            'Administrateur' => 0,
            'Vétérinaire' => 0,
            'Client' => 0,
            'total' => 0
        ];

        foreach ($results as $result) {
            $stats[$result['role_group']] = (int) $result['count'];
            $stats['total'] += (int) $result['count'];
        }

        return $stats;
    }

    /**
     * Récupère les derniers utilisateurs inscrits
     */
    public function findRecentUsers(int $limit = 10): array
    {
        return $this->createQueryBuilder('u')
            ->orderBy('u.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche avancée avec plusieurs critères
     */
    public function advancedSearch(array $criteria): array
    {
        $qb = $this->createQueryBuilder('u');
        
        if (!empty($criteria['name'])) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->like('u.firstName', ':name'),
                $qb->expr()->like('u.lastName', ':name')
            ))
            ->setParameter('name', '%' . $criteria['name'] . '%');
        }
        
        if (!empty($criteria['email'])) {
            $qb->andWhere('u.email LIKE :email')
               ->setParameter('email', '%' . $criteria['email'] . '%');
        }
        
        if (!empty($criteria['phone'])) {
            $qb->andWhere('u.phone LIKE :phone')
               ->setParameter('phone', '%' . $criteria['phone'] . '%');
        }
        
        if (!empty($criteria['roles'])) {
            $roleConditions = [];
            foreach ($criteria['roles'] as $index => $role) {
                $roleConditions[] = 'u.roles LIKE :role_' . $index;
                $qb->setParameter('role_' . $index, '%"' . $role . '"%');
            }
            $qb->andWhere($qb->expr()->orX(...$roleConditions));
        }
        
        if (!empty($criteria['date_from'])) {
            $qb->andWhere('u.createdAt >= :date_from')
               ->setParameter('date_from', $criteria['date_from']);
        }
        
        if (!empty($criteria['date_to'])) {
            $qb->andWhere('u.createdAt <= :date_to')
               ->setParameter('date_to', $criteria['date_to']);
        }

        // Tri
        $sortField = $criteria['sort_field'] ?? 'u.createdAt';
        $sortDirection = $criteria['sort_direction'] ?? 'DESC';
        
        $qb->orderBy($sortField, $sortDirection);

        return $qb->getQuery()->getResult();
    }

    /**
     * Trouve les utilisateurs par rôle
     */
    public function findByRole(string $role): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%"' . $role . '"%')
            ->orderBy('u.lastName', 'ASC')
            ->getQuery()
            ->getResult();
    }
}