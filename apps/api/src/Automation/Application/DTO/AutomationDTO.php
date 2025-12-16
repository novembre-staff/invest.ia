<?php

declare(strict_types=1);

namespace App\Automation\Application\DTO;

use App\Automation\Domain\Model\Automation;

final readonly class AutomationDTO
{
    public function __construct(
        public string $id,
        public string $userId,
        public string $name,
        public string $type,
        public string $status,
        public string $symbol,
        public ?string $interval,
        public ?array $dcaConfig,
        public ?array $gridConfig,
        public array $parameters,
        public int $executionCount,
        public float $totalInvested,
        public float $totalProfit,
        public float $roi,
        public ?string $lastExecutedAt,
        public ?string $nextExecutionAt,
        public string $createdAt,
        public string $updatedAt
    ) {
    }

    public static function fromDomain(Automation $automation): self
    {
        $dcaConfig = null;
        if ($automation->getDcaConfig() !== null) {
            $dca = $automation->getDcaConfig();
            $dcaConfig = [
                'amount_per_purchase' => $dca->getAmountPerPurchase(),
                'interval' => $dca->getInterval()->value,
                'max_total_investment' => $dca->getMaxTotalInvestment(),
                'max_executions' => $dca->getMaxExecutions(),
                'end_date' => $dca->getEndDate()?->format('Y-m-d H:i:s')
            ];
        }

        $gridConfig = null;
        if ($automation->getGridConfig() !== null) {
            $grid = $automation->getGridConfig();
            $gridConfig = [
                'lower_price' => $grid->getLowerPrice(),
                'upper_price' => $grid->getUpperPrice(),
                'grid_levels' => $grid->getGridLevels(),
                'quantity_per_grid' => $grid->getQuantityPerGrid(),
                'is_arithmetic' => $grid->isArithmetic(),
                'grid_prices' => $grid->calculateGridPrices(),
                'total_investment' => $grid->getTotalInvestment()
            ];
        }

        return new self(
            id: $automation->getId()->toString(),
            userId: $automation->getUserId()->toString(),
            name: $automation->getName(),
            type: $automation->getType()->value,
            status: $automation->getStatus()->value,
            symbol: $automation->getSymbol()->toString(),
            interval: $automation->getInterval()?->value,
            dcaConfig: $dcaConfig,
            gridConfig: $gridConfig,
            parameters: $automation->getParameters(),
            executionCount: $automation->getExecutionCount(),
            totalInvested: $automation->getTotalInvested(),
            totalProfit: $automation->getTotalProfit(),
            roi: $automation->getReturnOnInvestment(),
            lastExecutedAt: $automation->getLastExecutedAt()?->format('Y-m-d H:i:s'),
            nextExecutionAt: $automation->getNextExecutionAt()?->format('Y-m-d H:i:s'),
            createdAt: $automation->getCreatedAt()->format('Y-m-d H:i:s'),
            updatedAt: $automation->getUpdatedAt()->format('Y-m-d H:i:s')
        );
    }
}
