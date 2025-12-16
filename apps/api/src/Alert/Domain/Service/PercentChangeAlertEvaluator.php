<?php

declare(strict_types=1);

namespace App\Alert\Domain\Service;

use App\Alert\Domain\Model\PriceAlert;
use App\Alert\Domain\ValueObject\AlertType;

/**
 * Evaluates percentage change alerts
 */
final class PercentChangeAlertEvaluator implements AlertEvaluatorInterface
{
    public function evaluate(PriceAlert $alert, float $currentValue, ?float $previousValue = null): bool
    {
        if ($previousValue === null || $previousValue === 0.0) {
            return false;
        }

        $percentChange = (($currentValue - $previousValue) / $previousValue) * 100;
        $targetPercent = $alert->getCondition()->getTargetValue();

        // Check if absolute change exceeds threshold
        return abs($percentChange) >= abs($targetPercent);
    }

    public function supports(AlertType $type): bool
    {
        return $type === AlertType::PRICE_CHANGE_PERCENT;
    }
}
