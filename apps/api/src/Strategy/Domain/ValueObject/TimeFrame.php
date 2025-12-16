<?php

declare(strict_types=1);

namespace App\Strategy\Domain\ValueObject;

enum TimeFrame: string
{
    case ONE_MINUTE = '1m';
    case FIVE_MINUTES = '5m';
    case FIFTEEN_MINUTES = '15m';
    case THIRTY_MINUTES = '30m';
    case ONE_HOUR = '1h';
    case FOUR_HOURS = '4h';
    case ONE_DAY = '1d';
    case ONE_WEEK = '1w';
    case ONE_MONTH = '1M';

    public function getMinutes(): int
    {
        return match ($this) {
            self::ONE_MINUTE => 1,
            self::FIVE_MINUTES => 5,
            self::FIFTEEN_MINUTES => 15,
            self::THIRTY_MINUTES => 30,
            self::ONE_HOUR => 60,
            self::FOUR_HOURS => 240,
            self::ONE_DAY => 1440,
            self::ONE_WEEK => 10080,
            self::ONE_MONTH => 43200,
        };
    }

    public function getSeconds(): int
    {
        return $this->getMinutes() * 60;
    }

    public function toBinanceInterval(): string
    {
        return $this->value;
    }
}
