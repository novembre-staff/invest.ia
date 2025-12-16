<?php

declare(strict_types=1);

namespace App\Alert\Domain\ValueObject;

enum AlertStatus: string
{
    case ACTIVE = 'active';
    case TRIGGERED = 'triggered';
    case CANCELLED = 'cancelled';
    case EXPIRED = 'expired';

    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    public function canBeCancelled(): bool
    {
        return $this === self::ACTIVE;
    }
}
