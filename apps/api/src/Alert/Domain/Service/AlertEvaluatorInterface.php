<?php

declare(strict_types=1);

namespace App\Alert\Domain\Service;

use App\Alert\Domain\Model\PriceAlert;
use App\Alert\Domain\ValueObject\AlertType;

interface AlertEvaluatorInterface
{
    /**
     * Evaluate if an alert condition is met
     * 
     * @return bool True if the alert should be triggered
     */
    public function evaluate(PriceAlert $alert, float $currentValue, ?float $previousValue = null): bool;

    /**
     * Check if this evaluator supports the given alert type
     */
    public function supports(AlertType $type): bool;
}
