<?php

declare(strict_types=1);

namespace App\Exchange\Domain\Repository;

use App\Exchange\Domain\Model\ExchangeConnection;
use App\Exchange\Domain\ValueObject\ExchangeConnectionId;
use App\Identity\Domain\ValueObject\UserId;

interface ExchangeConnectionRepositoryInterface
{
    public function save(ExchangeConnection $connection): void;

    public function findById(ExchangeConnectionId $id): ?ExchangeConnection;

    /**
     * Find all exchange connections for a specific user
     * 
     * @return ExchangeConnection[]
     */
    public function findByUserId(UserId $userId): array;

    /**
     * Find a specific user's connection to a specific exchange
     */
    public function findByUserIdAndExchangeName(UserId $userId, string $exchangeName): ?ExchangeConnection;

    public function delete(ExchangeConnection $connection): void;
}
