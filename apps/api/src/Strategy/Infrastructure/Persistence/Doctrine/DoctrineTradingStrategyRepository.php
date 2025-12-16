<?php

declare(strict_types=1);

namespace App\Strategy\Infrastructure\Persistence\Doctrine;

use App\Identity\Domain\ValueObject\UserId;
use App\Strategy\Domain\Model\TradingStrategy;
use App\Strategy\Domain\Repository\TradingStrategyRepositoryInterface;
use App\Strategy\Domain\ValueObject\StrategyId;
use App\Strategy\Domain\ValueObject\StrategyStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DoctrineTradingStrategyRepository extends ServiceEntityRepository implements TradingStrategyRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TradingStrategy::class);
    }

    public function save(TradingStrategy $strategy): void
    {
        $this->getEntityManager()->persist($strategy);
        $this->getEntityManager()->flush();
    }

    public function findById(StrategyId $id): ?TradingStrategy
    {
        return $this->find($id->getValue());
    }

    public function findByUserId(UserId $userId, ?int $limit = null): array
    {
        $qb = $this->createQueryBuilder('s')
            ->where('s.userId = :userId')
            ->setParameter('userId', $userId->getValue())
            ->orderBy('s.createdAt', 'DESC');

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    public function findActiveByUserId(UserId $userId): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.userId = :userId')
            ->andWhere('s.status = :status')
            ->setParameter('userId', $userId->getValue())
            ->setParameter('status', StrategyStatus::ACTIVE->value)
            ->orderBy('s.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByStatus(StrategyStatus $status, ?int $limit = null): array
    {
        $qb = $this->createQueryBuilder('s')
            ->where('s.status = :status')
            ->setParameter('status', $status->value)
            ->orderBy('s.createdAt', 'DESC');

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    public function delete(TradingStrategy $strategy): void
    {
        $this->getEntityManager()->remove($strategy);
        $this->getEntityManager()->flush();
    }
}
