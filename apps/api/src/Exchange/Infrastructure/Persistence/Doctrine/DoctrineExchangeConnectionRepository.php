<?php

declare(strict_types=1);

namespace App\Exchange\Infrastructure\Persistence\Doctrine;

use App\Exchange\Domain\Model\ExchangeConnection;
use App\Exchange\Domain\Repository\ExchangeConnectionRepositoryInterface;
use App\Exchange\Domain\ValueObject\ExchangeConnectionId;
use App\Identity\Domain\ValueObject\UserId;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineExchangeConnectionRepository implements ExchangeConnectionRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function save(ExchangeConnection $connection): void
    {
        $this->entityManager->persist($connection);
        $this->entityManager->flush();
    }

    public function findById(ExchangeConnectionId $id): ?ExchangeConnection
    {
        return $this->entityManager
            ->getRepository(ExchangeConnection::class)
            ->findOneBy(['id' => $id]);
    }

    public function findByUserId(UserId $userId): array
    {
        return $this->entityManager
            ->getRepository(ExchangeConnection::class)
            ->findBy(['userId' => $userId], ['connectedAt' => 'DESC']);
    }

    public function findByUserIdAndExchangeName(UserId $userId, string $exchangeName): ?ExchangeConnection
    {
        return $this->entityManager
            ->getRepository(ExchangeConnection::class)
            ->findOneBy([
                'userId' => $userId,
                'exchangeName' => $exchangeName,
                'isActive' => true
            ]);
    }

    public function delete(ExchangeConnection $connection): void
    {
        $this->entityManager->remove($connection);
        $this->entityManager->flush();
    }
}
