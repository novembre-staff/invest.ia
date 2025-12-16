<?php

declare(strict_types=1);

namespace App\Market\Domain\ValueObject;

/**
 * Volume traded over a period
 */
final readonly class Volume
{
    public function __construct(
        private float $value
    ) {
        if ($value < 0) {
            throw new \InvalidArgumentException('Volume cannot be negative');
        }
    }

    public static function fromFloat(float $value): self
    {
        return new self($value);
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function format(): string
    {
        if ($this->value >= 1_000_000_000) {
            return number_format($this->value / 1_000_000_000, 2) . 'B';
        }
        
        if ($this->value >= 1_000_000) {
            return number_format($this->value / 1_000_000, 2) . 'M';
        }
        
        if ($this->value >= 1_000) {
            return number_format($this->value / 1_000, 2) . 'K';
        }
        
        return number_format($this->value, 2);
    }
}
