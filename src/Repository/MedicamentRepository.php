<?php
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
    }
}