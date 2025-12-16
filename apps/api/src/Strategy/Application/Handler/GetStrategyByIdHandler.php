<?php

declare(strict_types=1);

namespace App\Strategy\Application\Handler;

use App\Strategy\Application\DTO\TradingStrategyDTO;
use App\Strategy\Application\Query\GetStrategyById;
use App\Strategy\Domain\Repository\TradingStrategyRepositoryInterface;
use App\Strategy\Domain\ValueObject\StrategyId;

final readonly class GetStrategyByIdHandler
{
    public function __construct(
        private TradingStrategyRepositoryInterface $strategyRepository
    ) {
    }

    public function __invoke(GetStrategyById $query): TradingStrategyDTO
    {
        $strategy = $this->strategyRepository->findById(StrategyId::fromString($query->strategyId));

        if (!$strategy) {
            throw new \DomainException('Strategy not found');
        }

        // Verify ownership
        if ($strategy->getUserId()->getValue() !== $query->userId) {
            throw new \DomainException('Unauthorized access to strategy');
        }

        return TradingStrategyDTO::fromDomain($strategy);
    }
}
