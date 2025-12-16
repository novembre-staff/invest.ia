<?php

declare(strict_types=1);

namespace App\Risk\Domain\Service;

use App\Identity\Domain\ValueObject\UserId;
use App\Risk\Domain\ValueObject\ExposureSnapshot;

interface ExposureMonitorInterface
{
    /**
     * Get current exposure snapshot for a user
     */
    public function getCurrentExposure(UserId $userId): ExposureSnapshot;

    /**
     * Check if a new position would exceed exposure limits
     */
    public function wouldExceedLimit(
        UserId $userId,
        string $symbol,
        float $positionSizePercent
    ): bool;

    /**
     * Get available capacity for new positions
     */
    public function getAvailableCapacity(UserId $userId): float;
}
