<?php

declare(strict_types=1);

namespace App\Automation\Domain\ValueObject;

enum ExecutionInterval: string
{
    case EVERY_MINUTE = '1m';
    case EVERY_5_MINUTES = '5m';
    case EVERY_15_MINUTES = '15m';
    case EVERY_30_MINUTES = '30m';
    case HOURLY = '1h';
    case EVERY_4_HOURS = '4h';
    case EVERY_8_HOURS = '8h';
    case DAILY = '1d';
    case WEEKLY = '1w';
    case MONTHLY = '1M';

    public function getMinutes(): int
    {
        return match ($this) {
            self::EVERY_MINUTE => 1,
            self::EVERY_5_MINUTES => 5,
            self::EVERY_15_MINUTES => 15,
            self::EVERY_30_MINUTES => 30,
            self::HOURLY => 60,
            self::EVERY_4_HOURS => 240,
            self::EVERY_8_HOURS => 480,
            self::DAILY => 1440,
            self::WEEKLY => 10080,
            self::MONTHLY => 43200, // Approximation 30 jours
        };
    }

    public function getCronExpression(): string
    {
        return match ($this) {
            self::EVERY_MINUTE => '* * * * *',
            self::EVERY_5_MINUTES => '*/5 * * * *',
            self::EVERY_15_MINUTES => '*/15 * * * *',
            self::EVERY_30_MINUTES => '*/30 * * * *',
            self::HOURLY => '0 * * * *',
            self::EVERY_4_HOURS => '0 */4 * * *',
            self::EVERY_8_HOURS => '0 */8 * * *',
            self::DAILY => '0 0 * * *',
            self::WEEKLY => '0 0 * * 0',
            self::MONTHLY => '0 0 1 * *',
        };
    }
}
