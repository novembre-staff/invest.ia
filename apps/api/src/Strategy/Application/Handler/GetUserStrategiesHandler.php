<?php

declare(strict_types=1);

namespace App\Strategy\Application\Handler;

use App\Identity\Domain\ValueObject\UserId;
use App\Strategy\Application\DTO\TradingStrategyDTO;
use App\Strategy\Application\Query\GetUserStrategies;
use App\Strategy\Domain\Repository\TradingStrategyRepositoryInterface;

final readonly class GetUserStrategiesHandler
{
    public function __construct(
        private TradingStrategyRepositoryInterface $strategyRepository
    ) {
    }

    public function __invoke(GetUserStrategies $query): array
    {
        $userId = UserId::fromString($query->userId);

        if ($query->activeOnly) {
            $strategies = $this->strategyRepository->findActiveByUserId($userId);
        } else {
            $strategies = $this->strategyRepository->findByUserId($userId, $query->limit);
        }

        return array_map(
            fn($strategy) => TradingStrategyDTO::fromDomain($strategy),
            $strategies
        );
    }
}
