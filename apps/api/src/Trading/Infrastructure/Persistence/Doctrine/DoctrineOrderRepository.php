<?php

declare(strict_types=1);

namespace App\Trading\Infrastructure\Persistence\Doctrine;

use App\Identity\Domain\ValueObject\UserId;
use App\Trading\Domain\Model\Order;
use App\Trading\Domain\Repository\OrderRepositoryInterface;
use App\Trading\Domain\ValueObject\OrderId;
use App\Trading\Domain\ValueObject\OrderStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
class DoctrineOrderRepository extends ServiceEntityRepository implements OrderRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    public function save(Order $order): void
    {
        $this->getEntityManager()->persist($order);
        $this->getEntityManager()->flush();
    }

    public function findById(OrderId $id): ?Order
    {
        return $this->find($id->getValue());
    }

    public function findByExchangeOrderId(string $exchangeOrderId): ?Order
    {
        return $this->createQueryBuilder('o')
            ->where('o.exchangeOrderId = :exchangeOrderId')
            ->setParameter('exchangeOrderId', $exchangeOrderId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByUserId(UserId $userId, ?int $limit = null): array
    {
        $qb = $this->createQueryBuilder('o')
            ->where('o.userId = :userId')
            ->setParameter('userId', $userId->getValue())
            ->orderBy('o.createdAt', 'DESC');

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    public function findActiveByUserId(UserId $userId): array
    {
        return $this->createQueryBuilder('o')
            ->where('o.userId = :userId')
            ->andWhere('o.status IN (:statuses)')
            ->setParameter('userId', $userId->getValue())
            ->setParameter('statuses', [
                OrderStatus::PENDING->value,
                OrderStatus::NEW->value,
                OrderStatus::PARTIALLY_FILLED->value,
            ])
            ->orderBy('o.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByUserIdAndSymbol(UserId $userId, string $symbol, ?int $limit = null): array
    {
        $qb = $this->createQueryBuilder('o')
            ->where('o.userId = :userId')
            ->andWhere('o.symbol = :symbol')
            ->setParameter('userId', $userId->getValue())
            ->setParameter('symbol', $symbol)
            ->orderBy('o.createdAt', 'DESC');

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    public function findByStatus(OrderStatus $status): array
    {
        return $this->createQueryBuilder('o')
            ->where('o.status = :status')
            ->setParameter('status', $status->value)
            ->orderBy('o.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function delete(Order $order): void
    {
        $this->getEntityManager()->remove($order);
        $this->getEntityManager()->flush();
    }
}
