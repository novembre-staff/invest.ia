<?php

declare(strict_types=1);

namespace App\Alert\Domain\ValueObject;

final readonly class AlertCondition
{
    public function __construct(
        public float $targetValue,
        public ?float $comparisonValue = null,
        public ?int $timeframeMinutes = null
    ) {
        if ($targetValue < 0) {
            throw new \InvalidArgumentException('Target value must be non-negative');
        }
    }

    public static function priceAbove(float $price): self
    {
        return new self($price);
    }

    public static function priceBelow(float $price): self
    {
        return new self($price);
    }

    public static function percentChange(float $percentage, int $timeframeMinutes = 60): self
    {
        if ($percentage < -100 || $percentage > 1000) {
            throw new \InvalidArgumentException('Percentage must be between -100 and 1000');
        }

        return new self($percentage, null, $timeframeMinutes);
    }

    public static function volumeSpike(float $multiplier, int $timeframeMinutes = 60): self
    {
        if ($multiplier < 1) {
            throw new \InvalidArgumentException('Volume multiplier must be >= 1');
        }

        return new self($multiplier, null, $timeframeMinutes);
    }

    public static function portfolioValue(float $value): self
    {
        return new self($value);
    }

    public function getTargetValue(): float
    {
        return $this->targetValue;
    }

    public function getTimeframeMinutes(): ?int
    {
        return $this->timeframeMinutes;
    }
}
