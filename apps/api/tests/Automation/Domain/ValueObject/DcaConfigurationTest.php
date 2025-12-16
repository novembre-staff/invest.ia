<?php

declare(strict_types=1);

namespace Tests\Automation\Domain\ValueObject;

use App\Automation\Domain\ValueObject\DcaConfiguration;
use App\Automation\Domain\ValueObject\ExecutionInterval;
use PHPUnit\Framework\TestCase;

class DcaConfigurationTest extends TestCase
{
    public function test_creates_valid_dca_configuration(): void
    {
        $config = new DcaConfiguration(
            amountPerPurchase: 100.0,
            interval: ExecutionInterval::DAILY,
            maxTotalInvestment: 3000.0,
            maxExecutions: 30
        );

        $this->assertEquals(100.0, $config->getAmountPerPurchase());
        $this->assertEquals(ExecutionInterval::DAILY, $config->getInterval());
        $this->assertEquals(3000.0, $config->getMaxTotalInvestment());
        $this->assertEquals(30, $config->getMaxExecutions());
    }

    public function test_validates_positive_amount(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new DcaConfiguration(
            amountPerPurchase: -100.0,
            interval: ExecutionInterval::DAILY
        );
    }

    public function test_validates_max_investment_greater_than_amount(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new DcaConfiguration(
            amountPerPurchase: 100.0,
            interval: ExecutionInterval::DAILY,
            maxTotalInvestment: 50.0
        );
    }

    public function test_checks_investment_limit(): void
    {
        $config = new DcaConfiguration(
            amountPerPurchase: 100.0,
            interval: ExecutionInterval::DAILY,
            maxTotalInvestment: 300.0
        );

        $this->assertFalse($config->hasReachedInvestmentLimit(200.0));
        $this->assertTrue($config->hasReachedInvestmentLimit(300.0));
        $this->assertTrue($config->hasReachedInvestmentLimit(350.0));
    }

    public function test_checks_execution_limit(): void
    {
        $config = new DcaConfiguration(
            amountPerPurchase: 100.0,
            interval: ExecutionInterval::DAILY,
            maxExecutions: 10
        );

        $this->assertFalse($config->hasReachedExecutionLimit(5));
        $this->assertTrue($config->hasReachedExecutionLimit(10));
        $this->assertTrue($config->hasReachedExecutionLimit(15));
    }

    public function test_checks_end_date(): void
    {
        $endDate = new \DateTimeImmutable('+7 days');
        
        $config = new DcaConfiguration(
            amountPerPurchase: 100.0,
            interval: ExecutionInterval::DAILY,
            endDate: $endDate
        );

        $now = new \DateTimeImmutable();
        $this->assertFalse($config->hasReachedEndDate($now));

        $future = new \DateTimeImmutable('+10 days');
        $this->assertTrue($config->hasReachedEndDate($future));
    }

    public function test_should_stop_when_any_limit_reached(): void
    {
        $config = new DcaConfiguration(
            amountPerPurchase: 100.0,
            interval: ExecutionInterval::DAILY,
            maxTotalInvestment: 1000.0,
            maxExecutions: 10
        );

        $now = new \DateTimeImmutable();
        
        // No limits reached
        $this->assertFalse($config->shouldStop(500.0, 5, $now));
        
        // Investment limit reached
        $this->assertTrue($config->shouldStop(1000.0, 5, $now));
        
        // Execution limit reached
        $this->assertTrue($config->shouldStop(500.0, 10, $now));
    }
}
