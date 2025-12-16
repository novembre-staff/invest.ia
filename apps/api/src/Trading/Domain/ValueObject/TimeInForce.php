<?php

declare(strict_types=1);

namespace App\Trading\Domain\ValueObject;

enum TimeInForce: string
{
    case GTC = 'GTC'; // Good Till Cancel
    case IOC = 'IOC'; // Immediate Or Cancel
    case FOK = 'FOK'; // Fill Or Kill

    public function getDisplayName(): string
    {
        return match($this) {
            self::GTC => 'Good Till Cancel',
            self::IOC => 'Immediate Or Cancel',
            self::FOK => 'Fill Or Kill',
        };
    }

    public function getDescription(): string
    {
        return match($this) {
            self::GTC => 'Order remains active until filled or cancelled',
            self::IOC => 'Order executes immediately, unfilled portion is cancelled',
            self::FOK => 'Order must be filled entirely or cancelled immediately',
        };
    }
}
