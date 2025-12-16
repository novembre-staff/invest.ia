<?php

declare(strict_types=1);

namespace App\Analytics\Infrastructure\Service;

use App\Analytics\Domain\Service\AllocationCalculatorInterface;
use App\Analytics\Domain\ValueObject\AssetAllocation;
use App\Identity\Domain\ValueObject\UserId;
use App\Portfolio\Infrastructure\Adapter\PortfolioProviderInterface;
use Psr\Log\LoggerInterface;

class SimpleAllocationCalculator implements AllocationCalculatorInterface
{
    public function __construct(
        private PortfolioProviderInterface $portfolioProvider,
        private LoggerInterface $logger
    ) {
    }

    public function calculateAllocation(UserId $userId): AssetAllocation
    {
        $portfolio = $this->portfolioProvider->getPortfolio($userId->toString());
        $balances = $portfolio->getBalances();

        $totalValue = 0.0;
        $values = [];

        // Calculate total value and individual asset values
        foreach ($balances as $symbol => $balance) {
            $value = $balance->getValueUSDT();
            $totalValue += $value;
            $values[$symbol] = $value;
        }

        // Calculate percentages
        $allocations = [];
        if ($totalValue > 0) {
            foreach ($values as $symbol => $value) {
                $allocations[$symbol] = ($value / $totalValue) * 100;
            }
        }

        // Sort by allocation descending
        arsort($allocations);

        return new AssetAllocation($allocations, $totalValue);
    }

    public function getHistoricalAllocation(
        UserId $userId,
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate,
        int $dataPoints = 30
    ): array {
        // TODO: Implement historical allocation retrieval
        // This would require storing periodic snapshots of portfolio balances
        
        $this->logger->info('Historical allocation requested', [
            'user_id' => $userId->toString(),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'data_points' => $dataPoints
        ]);

        return [];
    }
}
