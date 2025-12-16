<?php

declare(strict_types=1);

namespace App\Trading\Domain\ValueObject;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case NEW = 'new';
    case PARTIALLY_FILLED = 'partially_filled';
    case FILLED = 'filled';
    case CANCELLED = 'cancelled';
    case REJECTED = 'rejected';
    case EXPIRED = 'expired';

    public function getDisplayName(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::NEW => 'New',
            self::PARTIALLY_FILLED => 'Partially Filled',
            self::FILLED => 'Filled',
            self::CANCELLED => 'Cancelled',
            self::REJECTED => 'Rejected',
            self::EXPIRED => 'Expired',
        };
    }

    public function isActive(): bool
    {
        return match($this) {
            self::PENDING, self::NEW, self::PARTIALLY_FILLED => true,
            default => false,
        };
    }

    public function isFinal(): bool
    {
        return match($this) {
            self::FILLED, self::CANCELLED, self::REJECTED, self::EXPIRED => true,
            default => false,
        };
    }

    public function canBeCancelled(): bool
    {
        return $this->isActive();
    }

    /**
     * Map from Binance status
     */
    public static function fromBinanceStatus(string $status): self
    {
        return match($status) {
            'NEW' => self::NEW,
            'PARTIALLY_FILLED' => self::PARTIALLY_FILLED,
            'FILLED' => self::FILLED,
            'CANCELED' => self::CANCELLED,
            'REJECTED' => self::REJECTED,
            'EXPIRED' => self::EXPIRED,
            default => self::PENDING,
        };
    }
}
