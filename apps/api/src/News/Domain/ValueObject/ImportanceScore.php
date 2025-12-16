<?php

declare(strict_types=1);

namespace App\News\Domain\ValueObject;

/**
 * Importance score from 0 (low) to 100 (critical)
 */
final readonly class ImportanceScore
{
    private function __construct(
        private int $value
    ) {
        if ($value < 0 || $value > 100) {
            throw new \InvalidArgumentException('Importance score must be between 0 and 100');
        }
    }

    public static function fromInt(int $value): self
    {
        return new self($value);
    }

    public static function low(): self
    {
        return new self(25);
    }

    public static function medium(): self
    {
        return new self(50);
    }

    public static function high(): self
    {
        return new self(75);
    }

    public static function critical(): self
    {
        return new self(95);
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function isLow(): bool
    {
        return $this->value < 40;
    }

    public function isMedium(): bool
    {
        return $this->value >= 40 && $this->value < 70;
    }

    public function isHigh(): bool
    {
        return $this->value >= 70 && $this->value < 90;
    }

    public function isCritical(): bool
    {
        return $this->value >= 90;
    }

    public function getLevel(): string
    {
        return match(true) {
            $this->isCritical() => 'critical',
            $this->isHigh() => 'high',
            $this->isMedium() => 'medium',
            default => 'low',
        };
    }

    public function shouldAlert(): bool
    {
        return $this->value >= 75;
    }
}
