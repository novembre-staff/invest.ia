<?php

declare(strict_types=1);

namespace App\Strategy\Domain\Repository;

use App\Identity\Domain\ValueObject\UserId;
use App\Strategy\Domain\Model\TradingStrategy;
use App\Strategy\Domain\ValueObject\StrategyId;
use App\Strategy\Domain\ValueObject\StrategyStatus;

interface TradingStrategyRepositoryInterface
{
    public function save(TradingStrategy $strategy): void;

    public function findById(StrategyId $id): ?TradingStrategy;

    public function findByUserId(UserId $userId, ?int $limit = null): array;

    public function findActiveByUserId(UserId $userId): array;

    public function findByStatus(StrategyStatus $status, ?int $limit = null): array;

    public function delete(TradingStrategy $strategy): void;
}
