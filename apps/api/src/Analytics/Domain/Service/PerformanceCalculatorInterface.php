<?php

declare(strict_types=1);

namespace App\Analytics\Domain\Service;

use App\Analytics\Domain\ValueObject\PerformanceMetrics;
use App\Identity\Domain\ValueObject\UserId;

interface PerformanceCalculatorInterface
{
    /**
     * Calculate performance metrics for a user's portfolio
     */
    public function calculateMetrics(
        UserId $userId,
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate
    ): PerformanceMetrics;

    /**
     * Calculate returns for different periods
     */
    public function calculateReturns(
        UserId $userId,
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate
    ): array;
}
