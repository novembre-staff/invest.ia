<?php

declare(strict_types=1);

namespace App\Strategy\Domain\Service;

use App\Strategy\Domain\ValueObject\Indicator;

interface IndicatorCalculatorInterface
{
    /**
     * Calculate indicator values for given price data
     * 
     * @param Indicator $indicator The indicator to calculate
     * @param array $priceData Array of OHLCV data: [['time' => timestamp, 'open' => float, 'high' => float, 'low' => float, 'close' => float, 'volume' => float]]
     * @param array $parameters Indicator-specific parameters
     * @return array Calculated indicator values
     */
    public function calculate(Indicator $indicator, array $priceData, array $parameters): array;

    /**
     * Check if calculator supports given indicator
     */
    public function supports(Indicator $indicator): bool;
}
