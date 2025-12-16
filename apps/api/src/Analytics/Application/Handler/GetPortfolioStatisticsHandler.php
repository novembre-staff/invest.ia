<?php

declare(strict_types=1);

namespace App\Analytics\Application\Handler;

use App\Analytics\Application\Query\GetPortfolioStatistics;
use App\Analytics\Domain\Service\AllocationCalculatorInterface;
use App\Analytics\Domain\Service\PerformanceCalculatorInterface;
use App\Analytics\Domain\ValueObject\TimePeriod;
use App\Identity\Domain\ValueObject\UserId;

class GetPortfolioStatisticsHandler
{
    public function __construct(
        private PerformanceCalculatorInterface $performanceCalculator,
        private AllocationCalculatorInterface $allocationCalculator
    ) {
    }

    public function __invoke(GetPortfolioStatistics $query): array
    {
        $userId = UserId::fromString($query->userId);
        $period = TimePeriod::from($query->period);

        $endDate = new \DateTimeImmutable();
        $startDate = $period->getStartDate($endDate) ?? new \DateTimeImmutable('-30 days');

        $metrics = $this->performanceCalculator->calculateMetrics($userId, $startDate, $endDate);
        $allocation = $this->allocationCalculator->calculateAllocation($userId);

        return [
            'period' => $period->value,
            'start_date' => $startDate->format('Y-m-d H:i:s'),
            'end_date' => $endDate->format('Y-m-d H:i:s'),
            'metrics' => $metrics->toArray(),
            'allocation' => [
                'allocations' => $allocation->getAllocations(),
                'total_value' => $allocation->getTotalValue(),
                'top_assets' => $allocation->getTopAssets(5),
                'diversification_score' => $allocation->getDiversificationScore()
            ]
        ];
    }
}
