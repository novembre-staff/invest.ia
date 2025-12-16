<?php

declare(strict_types=1);

namespace App\Trading\Domain\ValueObject;

enum OrderSide: string
{
    case BUY = 'buy';
    case SELL = 'sell';

    public function getDisplayName(): string
    {
        return match($this) {
            self::BUY => 'Buy',
            self::SELL => 'Sell',
        };
    }

    public function toBinanceSide(): string
    {
        return match($this) {
            self::BUY => 'BUY',
            self::SELL => 'SELL',
        };
    }

    public function isOpposite(self $other): bool
    {
        return $this !== $other;
    }
}
