<?php

declare(strict_types=1);

namespace App\Analytics\Domain\Service;

use App\Analytics\Domain\ValueObject\AssetAllocation;
use App\Identity\Domain\ValueObject\UserId;

interface AllocationCalculatorInterface
{
    /**
     * Calculate current asset allocation for a user
     */
    public function calculateAllocation(UserId $userId): AssetAllocation;

    /**
     * Get historical allocation snapshots
     */
    public function getHistoricalAllocation(
        UserId $userId,
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate,
        int $dataPoints = 30
    ): array;
}
