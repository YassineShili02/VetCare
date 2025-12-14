<?php

namespace App\Repository;

use App\Entity\PasswordResetToken;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<PasswordResetToken>
 */
class PasswordResetTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PasswordResetToken::class);
    }

    public function save(PasswordResetToken $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PasswordResetToken $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Trouve un token actif pour un utilisateur et un code donné
     */
    public function findActiveToken(User $user, string $code): ?PasswordResetToken
    {
        return $this->createQueryBuilder('t')
            ->where('t.user = :user')
            ->andWhere('t.verificationCode = :code')
            ->andWhere('t.isUsed = false')
            ->andWhere('t.expiresAt > :now')
            ->setParameter('user', $user)
            ->setParameter('code', $code)
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('t.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Trouve un token de réinitialisation valide
     */
    public function findValidResetToken(string $token): ?PasswordResetToken
    {
        return $this->createQueryBuilder('t')
            ->where('t.verificationCode = :token')
            ->andWhere('t.isUsed = false')
            ->andWhere('t.expiresAt > :now')
            ->setParameter('token', $token)
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Trouve les tokens actifs pour un utilisateur
     */
    public function findActiveTokens(User $user): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.user = :user')
            ->andWhere('t.isUsed = false')
            ->andWhere('t.expiresAt > :now')
            ->setParameter('user', $user)
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte les tentatives récentes pour un utilisateur
     */
    public function countRecentAttempts(User $user, \DateTimeImmutable $since): int
    {
        return (int) $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.user = :user')
            ->andWhere('t.createdAt >= :since')
            ->setParameter('user', $user)
            ->setParameter('since', $since)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Compte les tentatives échouées pour un utilisateur
     */
    public function countFailedAttempts(User $user, \DateTimeImmutable $since): int
    {
        return (int) $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.user = :user')
            ->andWhere('t.createdAt >= :since')
            ->andWhere('t.isUsed = false')
            ->andWhere('t.expiresAt <= :now OR t.attempts >= 3')
            ->setParameter('user', $user)
            ->setParameter('since', $since)
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Trouve la dernière tentative pour un utilisateur
     */
    public function findLastAttempt(User $user): ?PasswordResetToken
    {
        return $this->createQueryBuilder('t')
            ->where('t.user = :user')
            ->orderBy('t.createdAt', 'DESC')
            ->setParameter('user', $user)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Trouve le dernier code généré pour un utilisateur (pour le débogage)
     */
    public function findLastGeneratedCode(User $user): ?string
    {
        $token = $this->createQueryBuilder('t')
            ->where('t.user = :user')
            ->orderBy('t.createdAt', 'DESC')
            ->setParameter('user', $user)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $token ? $token->getVerificationCode() : null;
    }

    /**
     * Nettoie les tokens expirés
     */
    public function cleanupExpiredTokens(int $days = 7): int
    {
        $dateLimit = (new \DateTimeImmutable())->modify("-{$days} days");
        
        return $this->createQueryBuilder('t')
            ->delete()
            ->where('t.expiresAt < :limit')
            ->setParameter('limit', $dateLimit)
            ->getQuery()
            ->execute();
    }

    /**
     * Désactive tous les tokens actifs d'un utilisateur
     */
    public function deactivateUserTokens(User $user): int
    {
        return $this->createQueryBuilder('t')
            ->update()
            ->set('t.isUsed', true)
            ->set('t.usedAt', ':now')
            ->where('t.user = :user')
            ->andWhere('t.isUsed = false')
            ->setParameter('user', $user)
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->execute();
    }

    /**
     * Trouve les tokens par type
     */
    public function findByType(string $type, bool $activeOnly = true): array
    {
        $qb = $this->createQueryBuilder('t')
            ->where('t.tokenType = :type')
            ->setParameter('type', $type);

        if ($activeOnly) {
            $qb->andWhere('t.isUsed = false')
               ->andWhere('t.expiresAt > :now')
               ->setParameter('now', new \DateTimeImmutable());
        }

        return $qb->orderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Statistiques d'utilisation des tokens
     */
    public function getStatistics(\DateTimeImmutable $startDate = null, \DateTimeImmutable $endDate = null): array
    {
        if ($startDate === null) {
            $startDate = (new \DateTimeImmutable())->modify('-30 days');
        }
        
        if ($endDate === null) {
            $endDate = new \DateTimeImmutable();
        }

        $qb = $this->createQueryBuilder('t')
            ->select([
                'COUNT(t.id) as total_tokens',
                'SUM(CASE WHEN t.isUsed = true THEN 1 ELSE 0 END) as used_tokens',
                'SUM(CASE WHEN t.expiresAt < :now AND t.isUsed = false THEN 1 ELSE 0 END) as expired_tokens',
                'AVG(t.attempts) as avg_attempts',
                'COUNT(DISTINCT t.user) as unique_users'
            ])
            ->where('t.createdAt BETWEEN :start AND :end')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->setParameter('now', new \DateTimeImmutable());

        $stats = $qb->getQuery()->getSingleResult();

        // Statistiques par type de token
        $typeStats = $this->createQueryBuilder('t')
            ->select([
                't.tokenType as token_type',
                'COUNT(t.id) as count',
                'SUM(CASE WHEN t.isUsed = true THEN 1 ELSE 0 END) as used_count'
            ])
            ->where('t.createdAt BETWEEN :start AND :end')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->groupBy('t.tokenType')
            ->getQuery()
            ->getResult();

        return [
            'total_tokens' => (int) $stats['total_tokens'],
            'used_tokens' => (int) $stats['used_tokens'],
            'expired_tokens' => (int) $stats['expired_tokens'],
            'active_tokens' => (int) $stats['total_tokens'] - (int) $stats['used_tokens'] - (int) $stats['expired_tokens'],
            'avg_attempts' => (float) $stats['avg_attempts'],
            'unique_users' => (int) $stats['unique_users'],
            'usage_rate' => $stats['total_tokens'] > 0 ? 
                round(((int) $stats['used_tokens'] / (int) $stats['total_tokens']) * 100, 2) : 0,
            'by_type' => $typeStats,
        ];
    }

    /**
     * Recherche avancée avec filtres
     */
    public function search(array $criteria = [], array $sort = []): array
    {
        $qb = $this->createQueryBuilder('t')
            ->leftJoin('t.user', 'u');

        // Filtres
        if (!empty($criteria['user'])) {
            if (is_numeric($criteria['user'])) {
                $qb->andWhere('t.user = :user')
                   ->setParameter('user', $criteria['user']);
            } else {
                $qb->andWhere($qb->expr()->orX(
                    $qb->expr()->like('u.email', ':user_search'),
                    $qb->expr()->like('u.firstName', ':user_search'),
                    $qb->expr()->like('u.lastName', ':user_search')
                ))
                ->setParameter('user_search', '%' . $criteria['user'] . '%');
            }
        }

        if (!empty($criteria['token_type'])) {
            $qb->andWhere('t.tokenType = :token_type')
               ->setParameter('token_type', $criteria['token_type']);
        }

        if (!empty($criteria['is_used'])) {
            $qb->andWhere('t.isUsed = :is_used')
               ->setParameter('is_used', $criteria['is_used'] === 'true');
        }

        if (!empty($criteria['expired'])) {
            if ($criteria['expired'] === 'true') {
                $qb->andWhere('t.expiresAt < :now')
                   ->setParameter('now', new \DateTimeImmutable());
            } else {
                $qb->andWhere('t.expiresAt >= :now')
                   ->setParameter('now', new \DateTimeImmutable());
            }
        }

        if (!empty($criteria['date_from'])) {
            $qb->andWhere('t.createdAt >= :date_from')
               ->setParameter('date_from', $criteria['date_from']);
        }

        if (!empty($criteria['date_to'])) {
            $qb->andWhere('t.createdAt <= :date_to')
               ->setParameter('date_to', $criteria['date_to']);
        }

        if (!empty($criteria['ip_address'])) {
            $qb->andWhere('t.ipAddress LIKE :ip_address')
               ->setParameter('ip_address', '%' . $criteria['ip_address'] . '%');
        }

        // Tri
        $sortField = $sort['field'] ?? 't.createdAt';
        $sortDirection = $sort['direction'] ?? 'DESC';

        $allowedSortFields = [
            't.id', 't.createdAt', 't.expiresAt', 't.usedAt', 
            't.attempts', 'u.email', 'u.firstName', 'u.lastName'
        ];

        if (in_array($sortField, $allowedSortFields)) {
            $qb->orderBy($sortField, $sortDirection);
        } else {
            $qb->orderBy('t.createdAt', 'DESC');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Trouve les tokens par plage de dates
     */
    public function findByDateRange(\DateTimeInterface $start, \DateTimeInterface $end): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.createdAt BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Vérifie si un utilisateur a un token actif
     */
    public function userHasActiveToken(User $user): bool
    {
        $count = (int) $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.user = :user')
            ->andWhere('t.isUsed = false')
            ->andWhere('t.expiresAt > :now')
            ->setParameter('user', $user)
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }

    /**
     * Récupère l'historique des tokens pour un utilisateur
     */
    public function getUserTokenHistory(User $user, int $limit = 20): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.user = :user')
            ->setParameter('user', $user)
            ->orderBy('t.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}