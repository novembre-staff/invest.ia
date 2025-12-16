<?php

declare(strict_types=1);

namespace App\Alert\Domain\Service;

use App\Alert\Domain\Model\PriceAlert;
use App\Alert\Domain\ValueObject\AlertType;

/**
 * Evaluates price-based alerts (PRICE_ABOVE, PRICE_BELOW)
 */
final class PriceAlertEvaluator implements AlertEvaluatorInterface
{
    public function evaluate(PriceAlert $alert, float $currentValue, ?float $previousValue = null): bool
    {
        $targetValue = $alert->getCondition()->getTargetValue();

        return match($alert->getType()) {
            AlertType::PRICE_ABOVE => $currentValue >= $targetValue,
            AlertType::PRICE_BELOW => $currentValue <= $targetValue,
            default => false,
        };
    }

    public function supports(AlertType $type): bool
    {
        return in_array($type, [
            AlertType::PRICE_ABOVE,
            AlertType::PRICE_BELOW,
        ], true);
    }
}
