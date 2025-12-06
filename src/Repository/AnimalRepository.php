<?php

namespace App\Repository;

use App\Entity\Animal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Animal>
 */
class AnimalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Animal::class);
    }

    public function findAllSorted(string $sort, string $order): array
    {
        $allowedSorts = [
            'nom' => 'a.nom',
            'type_animal' => 'a.type_animal',
            'sexe' => 'a.sexe',
            'poids' => 'a.poids',
            'couleur' => 'a.couleur',
            'date_naissance' => 'a.date_naissance',
            'date_enregistrement' => 'a.date_enregistrement',
        ];

        if (!isset($allowedSorts[$sort])) {
            $sort = 'date_enregistrement';
        }

        $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

        return $this->createQueryBuilder('a')
            ->orderBy($allowedSorts[$sort], $order)
            ->getQuery()
            ->getResult();
    }



    //    /**
    //     * @return Animal[] Returns an array of Animal objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Animal
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
