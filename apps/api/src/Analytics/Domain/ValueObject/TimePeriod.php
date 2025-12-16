<?php

declare(strict_types=1);

namespace App\Analytics\Domain\ValueObject;

enum TimePeriod: string
{
    case LAST_24_HOURS = '24h';
    case LAST_7_DAYS = '7d';
    case LAST_30_DAYS = '30d';
    case LAST_90_DAYS = '90d';
    case LAST_6_MONTHS = '6m';
    case LAST_YEAR = '1y';
    case ALL_TIME = 'all';
    case CUSTOM = 'custom';

    public function getDays(): ?int
    {
        return match ($this) {
            self::LAST_24_HOURS => 1,
            self::LAST_7_DAYS => 7,
            self::LAST_30_DAYS => 30,
            self::LAST_90_DAYS => 90,
            self::LAST_6_MONTHS => 180,
            self::LAST_YEAR => 365,
            self::ALL_TIME, self::CUSTOM => null,
        };
    }

    public function getStartDate(\DateTimeImmutable $from = null): ?\DateTimeImmutable
    {
        $from = $from ?? new \DateTimeImmutable();
        $days = $this->getDays();

        if ($days === null) {
            return null;
        }

        return $from->modify("-{$days} days");
    }
}
