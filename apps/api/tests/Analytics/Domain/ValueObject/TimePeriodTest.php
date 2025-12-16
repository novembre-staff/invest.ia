<?php

declare(strict_types=1);

namespace Tests\Analytics\Domain\ValueObject;

use App\Analytics\Domain\ValueObject\TimePeriod;
use PHPUnit\Framework\TestCase;

class TimePeriodTest extends TestCase
{
    public function test_gets_days_for_period(): void
    {
        $this->assertEquals(1, TimePeriod::LAST_24_HOURS->getDays());
        $this->assertEquals(7, TimePeriod::LAST_7_DAYS->getDays());
        $this->assertEquals(30, TimePeriod::LAST_30_DAYS->getDays());
        $this->assertEquals(90, TimePeriod::LAST_90_DAYS->getDays());
        $this->assertEquals(180, TimePeriod::LAST_6_MONTHS->getDays());
        $this->assertEquals(365, TimePeriod::LAST_YEAR->getDays());
        $this->assertNull(TimePeriod::ALL_TIME->getDays());
        $this->assertNull(TimePeriod::CUSTOM->getDays());
    }

    public function test_calculates_start_date(): void
    {
        $referenceDate = new \DateTimeImmutable('2024-01-31 12:00:00');

        $startDate = TimePeriod::LAST_7_DAYS->getStartDate($referenceDate);
        $this->assertEquals('2024-01-24', $startDate->format('Y-m-d'));

        $startDate = TimePeriod::LAST_30_DAYS->getStartDate($referenceDate);
        $this->assertEquals('2024-01-01', $startDate->format('Y-m-d'));
    }

    public function test_returns_null_for_all_time_period(): void
    {
        $startDate = TimePeriod::ALL_TIME->getStartDate();
        $this->assertNull($startDate);
    }

    public function test_returns_null_for_custom_period(): void
    {
        $startDate = TimePeriod::CUSTOM->getStartDate();
        $this->assertNull($startDate);
    }
}
