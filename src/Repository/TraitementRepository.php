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
    }
}