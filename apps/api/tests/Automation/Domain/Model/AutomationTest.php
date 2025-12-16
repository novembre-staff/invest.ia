<?php

declare(strict_types=1);

namespace Tests\Automation\Domain\Model;

use App\Automation\Domain\Model\Automation;
use App\Automation\Domain\ValueObject\AutomationType;
use App\Automation\Domain\ValueObject\AutomationStatus;
use App\Automation\Domain\ValueObject\DcaConfiguration;
use App\Automation\Domain\ValueObject\ExecutionInterval;
use App\Automation\Domain\ValueObject\GridConfiguration;
use App\Identity\Domain\ValueObject\UserId;
use App\Market\Domain\ValueObject\Symbol;
use PHPUnit\Framework\TestCase;

class AutomationTest extends TestCase
{
    public function test_creates_dca_automation(): void
    {
        $dcaConfig = new DcaConfiguration(
            amountPerPurchase: 100.0,
            interval: ExecutionInterval::DAILY,
            maxTotalInvestment: 3000.0,
            maxExecutions: 30
        );

        $automation = Automation::create(
            userId: UserId::generate(),
            name: 'BTC Daily DCA',
            type: AutomationType::DCA,
            symbol: Symbol::fromString('BTCUSDT'),
            interval: ExecutionInterval::DAILY,
            dcaConfig: $dcaConfig
        );

        $this->assertEquals(AutomationType::DCA, $automation->getType());
        $this->assertEquals(AutomationStatus::DRAFT, $automation->getStatus());
        $this->assertEquals('BTC Daily DCA', $automation->getName());
        $this->assertEquals('BTCUSDT', $automation->getSymbol()->toString());
        $this->assertEquals(100.0, $automation->getDcaConfig()->getAmountPerPurchase());
    }

    public function test_creates_grid_trading_automation(): void
    {
        $gridConfig = new GridConfiguration(
            lowerPrice: 40000.0,
            upperPrice: 50000.0,
            gridLevels: 10,
            quantityPerGrid: 0.01
        );

        $automation = Automation::create(
            userId: UserId::generate(),
            name: 'BTC Grid Bot',
            type: AutomationType::GRID_TRADING,
            symbol: Symbol::fromString('BTCUSDT'),
            gridConfig: $gridConfig
        );

        $this->assertEquals(AutomationType::GRID_TRADING, $automation->getType());
        $this->assertEquals(10, $automation->getGridConfig()->getGridLevels());
    }

    public function test_dca_requires_interval_and_config(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Automation::create(
            userId: UserId::generate(),
            name: 'Invalid DCA',
            type: AutomationType::DCA,
            symbol: Symbol::fromString('BTCUSDT')
        );
    }

    public function test_grid_trading_requires_config(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Automation::create(
            userId: UserId::generate(),
            name: 'Invalid Grid',
            type: AutomationType::GRID_TRADING,
            symbol: Symbol::fromString('BTCUSDT')
        );
    }

    public function test_activates_automation(): void
    {
        $dcaConfig = new DcaConfiguration(
            amountPerPurchase: 100.0,
            interval: ExecutionInterval::DAILY
        );

        $automation = Automation::create(
            userId: UserId::generate(),
            name: 'BTC DCA',
            type: AutomationType::DCA,
            symbol: Symbol::fromString('BTCUSDT'),
            interval: ExecutionInterval::DAILY,
            dcaConfig: $dcaConfig
        );

        $automation->activate();

        $this->assertEquals(AutomationStatus::ACTIVE, $automation->getStatus());
        $this->assertNotNull($automation->getNextExecutionAt());
    }

    public function test_pauses_active_automation(): void
    {
        $dcaConfig = new DcaConfiguration(
            amountPerPurchase: 100.0,
            interval: ExecutionInterval::DAILY
        );

        $automation = Automation::create(
            userId: UserId::generate(),
            name: 'BTC DCA',
            type: AutomationType::DCA,
            symbol: Symbol::fromString('BTCUSDT'),
            interval: ExecutionInterval::DAILY,
            dcaConfig: $dcaConfig
        );

        $automation->activate();
        $automation->pause();

        $this->assertEquals(AutomationStatus::PAUSED, $automation->getStatus());
        $this->assertNull($automation->getNextExecutionAt());
    }

    public function test_stops_automation(): void
    {
        $dcaConfig = new DcaConfiguration(
            amountPerPurchase: 100.0,
            interval: ExecutionInterval::DAILY
        );

        $automation = Automation::create(
            userId: UserId::generate(),
            name: 'BTC DCA',
            type: AutomationType::DCA,
            symbol: Symbol::fromString('BTCUSDT'),
            interval: ExecutionInterval::DAILY,
            dcaConfig: $dcaConfig
        );

        $automation->activate();
        $automation->stop();

        $this->assertEquals(AutomationStatus::STOPPED, $automation->getStatus());
    }

    public function test_records_execution(): void
    {
        $dcaConfig = new DcaConfiguration(
            amountPerPurchase: 100.0,
            interval: ExecutionInterval::DAILY
        );

        $automation = Automation::create(
            userId: UserId::generate(),
            name: 'BTC DCA',
            type: AutomationType::DCA,
            symbol: Symbol::fromString('BTCUSDT'),
            interval: ExecutionInterval::DAILY,
            dcaConfig: $dcaConfig
        );

        $automation->activate();
        $automation->recordExecution(investedAmount: 100.0, profit: 5.0);

        $this->assertEquals(1, $automation->getExecutionCount());
        $this->assertEquals(100.0, $automation->getTotalInvested());
        $this->assertEquals(5.0, $automation->getTotalProfit());
        $this->assertNotNull($automation->getLastExecutedAt());
    }

    public function test_calculates_roi(): void
    {
        $dcaConfig = new DcaConfiguration(
            amountPerPurchase: 100.0,
            interval: ExecutionInterval::DAILY
        );

        $automation = Automation::create(
            userId: UserId::generate(),
            name: 'BTC DCA',
            type: AutomationType::DCA,
            symbol: Symbol::fromString('BTCUSDT'),
            interval: ExecutionInterval::DAILY,
            dcaConfig: $dcaConfig
        );

        $automation->activate();
        $automation->recordExecution(investedAmount: 100.0, profit: 10.0);

        $this->assertEquals(10.0, $automation->getReturnOnInvestment());
    }

    public function test_completes_when_max_executions_reached(): void
    {
        $dcaConfig = new DcaConfiguration(
            amountPerPurchase: 100.0,
            interval: ExecutionInterval::DAILY,
            maxExecutions: 2
        );

        $automation = Automation::create(
            userId: UserId::generate(),
            name: 'BTC DCA',
            type: AutomationType::DCA,
            symbol: Symbol::fromString('BTCUSDT'),
            interval: ExecutionInterval::DAILY,
            dcaConfig: $dcaConfig
        );

        $automation->activate();
        $automation->recordExecution(100.0);
        $this->assertEquals(AutomationStatus::ACTIVE, $automation->getStatus());

        $automation->recordExecution(100.0);
        $this->assertEquals(AutomationStatus::COMPLETED, $automation->getStatus());
    }

    public function test_completes_when_max_investment_reached(): void
    {
        $dcaConfig = new DcaConfiguration(
            amountPerPurchase: 100.0,
            interval: ExecutionInterval::DAILY,
            maxTotalInvestment: 150.0
        );

        $automation = Automation::create(
            userId: UserId::generate(),
            name: 'BTC DCA',
            type: AutomationType::DCA,
            symbol: Symbol::fromString('BTCUSDT'),
            interval: ExecutionInterval::DAILY,
            dcaConfig: $dcaConfig
        );

        $automation->activate();
        $automation->recordExecution(100.0);
        $automation->recordExecution(100.0); // Total 200 >= 150

        $this->assertEquals(AutomationStatus::COMPLETED, $automation->getStatus());
    }

    public function test_should_execute_now_when_due(): void
    {
        $dcaConfig = new DcaConfiguration(
            amountPerPurchase: 100.0,
            interval: ExecutionInterval::EVERY_MINUTE
        );

        $automation = Automation::create(
            userId: UserId::generate(),
            name: 'BTC DCA',
            type: AutomationType::DCA,
            symbol: Symbol::fromString('BTCUSDT'),
            interval: ExecutionInterval::EVERY_MINUTE,
            dcaConfig: $dcaConfig
        );

        $automation->activate();
        
        // Should execute now since next execution is in the future but close
        $future = new \DateTimeImmutable('+2 minutes');
        $this->assertTrue($automation->shouldExecuteNow($future));
    }

    public function test_cannot_update_configuration_while_active(): void
    {
        $dcaConfig = new DcaConfiguration(
            amountPerPurchase: 100.0,
            interval: ExecutionInterval::DAILY
        );

        $automation = Automation::create(
            userId: UserId::generate(),
            name: 'BTC DCA',
            type: AutomationType::DCA,
            symbol: Symbol::fromString('BTCUSDT'),
            interval: ExecutionInterval::DAILY,
            dcaConfig: $dcaConfig
        );

        $automation->activate();

        $this->expectException(\DomainException::class);
        $automation->updateConfiguration(
            name: 'New Name',
            interval: ExecutionInterval::HOURLY
        );
    }
}
