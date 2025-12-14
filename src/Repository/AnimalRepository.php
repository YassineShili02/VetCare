<?php

    namespace App\Repository;

    use App\Entity\Animal;
    use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
    use Doctrine\Persistence\ManagerRegistry;
    use Doctrine\ORM\QueryBuilder;

    /**
     * @extends ServiceEntityRepository<Animal>
     */
    class AnimalRepository extends ServiceEntityRepository
    {
        public function __construct(ManagerRegistry $registry)
        {
            parent::__construct($registry, Animal::class);
        }

        /**
         * Retourne un tableau d'animaux triés
         */
        public function findAllSorted(string $sort, string $order): array
        {
            $qb = $this->createSortedQueryBuilder($sort, $order);
            return $qb->getQuery()->getResult();
        }

        /**
         * ✅ MÉTHODE pour la pagination - Retourne un QueryBuilder (pas Query)
         */
        public function findAllSortedQueryBuilder(string $sort = 'date_enregistrement', string $order = 'DESC'): QueryBuilder
        {
            return $this->createSortedQueryBuilder($sort, $order);
        }

        /**
         * 🔨 Méthode privée pour créer le QueryBuilder avec tri
         */
        private function createSortedQueryBuilder(string $sort, string $order): QueryBuilder
        {
            $allowedSorts = [
                'nom' => 'nom',
                'type_animal' => 'type_animal',
                'sexe' => 'sexe',
                'poids' => 'poids',
                'couleur' => 'couleur',
                'date_naissance' => 'date_naissance',
                'date_enregistrement' => 'date_enregistrement',
            ];

            $sortField = $allowedSorts[$sort] ?? 'date_enregistrement';
            $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

            return $this->createQueryBuilder('a')
                ->orderBy('a.' . $sortField, $order);
        }
    }