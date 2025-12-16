<?php

declare(strict_types=1);

namespace App\Market\Domain\ValueObject;

/**
 * Price with currency precision
 */
final readonly class Price
{
    public function __construct(
        private float $value,
        private string $currency = 'USDT'
    ) {
        if ($value < 0) {
            throw new \InvalidArgumentException('Price cannot be negative');
        }
    }

    public static function fromFloat(float $value, string $currency = 'USDT'): self
    {
        return new self($value, $currency);
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function add(Price $other): self
    {
        if ($this->currency !== $other->currency) {
            throw new \InvalidArgumentException('Cannot add prices with different currencies');
        }

        return new self($this->value + $other->value, $this->currency);
    }

    public function subtract(Price $other): self
    {
        if ($this->currency !== $other->currency) {
            throw new \InvalidArgumentException('Cannot subtract prices with different currencies');
        }

        return new self($this->value - $other->value, $this->currency);
    }

    public function multiply(float $factor): self
    {
        return new self($this->value * $factor, $this->currency);
    }

    public function percentageChange(Price $oldPrice): float
    {
        if ($this->currency !== $oldPrice->currency) {
            throw new \InvalidArgumentException('Cannot calculate percentage change with different currencies');
        }

        if ($oldPrice->value == 0) {
            return 0.0;
        }

        return (($this->value - $oldPrice->value) / $oldPrice->value) * 100;
    }

    public function format(int $decimals = 2): string
    {
        return number_format($this->value, $decimals, '.', ',') . ' ' . $this->currency;
    }

    public function equals(self $other): bool
    {
        return abs($this->value - $other->value) < 0.00000001 
            && $this->currency === $other->currency;
    }
}
