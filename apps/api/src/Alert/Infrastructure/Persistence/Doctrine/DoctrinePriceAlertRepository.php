<?php

declare(strict_types=1);

namespace App\Alert\Infrastructure\Persistence\Doctrine;

use App\Alert\Domain\Model\PriceAlert;
use App\Alert\Domain\Repository\PriceAlertRepositoryInterface;
use App\Alert\Domain\ValueObject\AlertStatus;
use App\Alert\Domain\ValueObject\AlertType;
use App\Alert\Domain\ValueObject\PriceAlertId;
use App\Identity\Domain\ValueObject\UserId;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PriceAlert>
 */
class DoctrinePriceAlertRepository extends ServiceEntityRepository implements PriceAlertRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PriceAlert::class);
    }

    public function save(PriceAlert $alert): void
    {
        $this->getEntityManager()->persist($alert);
        $this->getEntityManager()->flush();
    }

    public function findById(PriceAlertId $id): ?PriceAlert
    {
        return $this->find($id->getValue());
    }

    public function findByUserId(UserId $userId): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.userId = :userId')
            ->setParameter('userId', $userId->getValue())
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findActiveByUserId(UserId $userId): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.userId = :userId')
            ->andWhere('a.status = :status')
            ->setParameter('userId', $userId->getValue())
            ->setParameter('status', AlertStatus::ACTIVE->value)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findActiveBySymbol(string $symbol): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.symbol = :symbol')
            ->andWhere('a.status = :status')
            ->setParameter('symbol', $symbol)
            ->setParameter('status', AlertStatus::ACTIVE->value)
            ->getQuery()
            ->getResult();
    }

    public function findActiveByType(AlertType $type): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.type = :type')
            ->andWhere('a.status = :status')
            ->setParameter('type', $type->value)
            ->setParameter('status', AlertStatus::ACTIVE->value)
            ->getQuery()
            ->getResult();
    }

    public function findAllActive(): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.status = :status')
            ->setParameter('status', AlertStatus::ACTIVE->value)
            ->getQuery()
            ->getResult();
    }

    public function delete(PriceAlert $alert): void
    {
        $this->getEntityManager()->remove($alert);
        $this->getEntityManager()->flush();
    }
}
