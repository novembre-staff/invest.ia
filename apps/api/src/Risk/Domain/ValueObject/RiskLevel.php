<?php

declare(strict_types=1);

namespace App\Risk\Domain\ValueObject;

enum RiskLevel: string
{
    case VERY_LOW = 'very_low';
    case LOW = 'low';
    case MODERATE = 'moderate';
    case HIGH = 'high';
    case VERY_HIGH = 'very_high';

    public function getDescription(): string
    {
        return match ($this) {
            self::VERY_LOW => 'Very conservative - Minimal risk tolerance',
            self::LOW => 'Conservative - Low risk tolerance',
            self::MODERATE => 'Balanced - Moderate risk tolerance',
            self::HIGH => 'Aggressive - High risk tolerance',
            self::VERY_HIGH => 'Very aggressive - Maximum risk tolerance',
        };
    }

    public function getMaxDrawdownPercent(): float
    {
        return match ($this) {
            self::VERY_LOW => 5.0,
            self::LOW => 10.0,
            self::MODERATE => 20.0,
            self::HIGH => 30.0,
            self::VERY_HIGH => 50.0,
        };
    }

    public function getMaxPositionSizePercent(): float
    {
        return match ($this) {
            self::VERY_LOW => 5.0,
            self::LOW => 10.0,
            self::MODERATE => 20.0,
            self::HIGH => 30.0,
            self::VERY_HIGH => 50.0,
        };
    }

    public function getMaxDailyLossPercent(): float
    {
        return match ($this) {
            self::VERY_LOW => 2.0,
            self::LOW => 3.0,
            self::MODERATE => 5.0,
            self::HIGH => 10.0,
            self::VERY_HIGH => 15.0,
        };
    }
}
