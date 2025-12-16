<?php

declare(strict_types=1);

namespace Tests\Strategy\Domain\Model;

use App\Identity\Domain\ValueObject\UserId;
use App\Strategy\Domain\Model\TradingStrategy;
use App\Strategy\Domain\ValueObject\Indicator;
use App\Strategy\Domain\ValueObject\StrategyStatus;
use App\Strategy\Domain\ValueObject\StrategyType;
use App\Strategy\Domain\ValueObject\TimeFrame;
use PHPUnit\Framework\TestCase;

class TradingStrategyTest extends TestCase
{
    public function test_creates_strategy_with_valid_configuration(): void
    {
        $strategy = TradingStrategy::create(
            userId: UserId::generate(),
            name: 'RSI Oversold Strategy',
            description: 'Buy when RSI < 30, sell when RSI > 70',
            type: StrategyType::MEAN_REVERSION,
            symbols: ['BTCUSDT', 'ETHUSDT'],
            timeFrame: TimeFrame::ONE_HOUR,
            indicators: [
                ['indicator' => Indicator::RSI, 'parameters' => ['period' => 14]],
            ],
            entryRules: [
                ['field' => 'rsi', 'operator' => '<', 'value' => 30],
            ],
            exitRules: [
                ['field' => 'rsi', 'operator' => '>', 'value' => 70],
            ],
            positionSizePercent: 10.0,
            maxDrawdownPercent: 20.0
        );

        $this->assertEquals('RSI Oversold Strategy', $strategy->getName());
        $this->assertEquals(StrategyType::MEAN_REVERSION, $strategy->getType());
        $this->assertEquals(StrategyStatus::DRAFT, $strategy->getStatus());
        $this->assertEquals(['BTCUSDT', 'ETHUSDT'], $strategy->getSymbols());
        $this->assertEquals(TimeFrame::ONE_HOUR, $strategy->getTimeFrame());
        $this->assertEquals(10.0, $strategy->getPositionSizePercent());
    }

    public function test_validates_minimum_name_length(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Strategy name must be at least 3 characters');

        TradingStrategy::create(
            userId: UserId::generate(),
            name: 'AB',
            description: null,
            type: StrategyType::TREND_FOLLOWING,
            symbols: ['BTCUSDT'],
            timeFrame: TimeFrame::ONE_HOUR,
            indicators: [['indicator' => Indicator::SMA, 'parameters' => ['period' => 20]]],
            entryRules: [['field' => 'sma', 'operator' => '>', 'value' => 0]],
            exitRules: [['field' => 'sma', 'operator' => '<', 'value' => 0]]
        );
    }

    public function test_validates_at_least_one_symbol(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Strategy must have at least one symbol');

        TradingStrategy::create(
            userId: UserId::generate(),
            name: 'Test Strategy',
            description: null,
            type: StrategyType::TREND_FOLLOWING,
            symbols: [],
            timeFrame: TimeFrame::ONE_HOUR,
            indicators: [['indicator' => Indicator::SMA, 'parameters' => ['period' => 20]]],
            entryRules: [['field' => 'sma', 'operator' => '>', 'value' => 0]],
            exitRules: [['field' => 'sma', 'operator' => '<', 'value' => 0]]
        );
    }

    public function test_validates_position_size_percent(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Position size must be between 0 and 100%');

        TradingStrategy::create(
            userId: UserId::generate(),
            name: 'Test Strategy',
            description: null,
            type: StrategyType::TREND_FOLLOWING,
            symbols: ['BTCUSDT'],
            timeFrame: TimeFrame::ONE_HOUR,
            indicators: [['indicator' => Indicator::SMA, 'parameters' => ['period' => 20]]],
            entryRules: [['field' => 'sma', 'operator' => '>', 'value' => 0]],
            exitRules: [['field' => 'sma', 'operator' => '<', 'value' => 0]],
            positionSizePercent: 150.0
        );
    }

    public function test_starts_backtest_successfully(): void
    {
        $strategy = $this->createTestStrategy();

        $strategy->startBacktest();

        $this->assertEquals(StrategyStatus::BACKTESTING, $strategy->getStatus());
    }

    public function test_completes_backtest_with_passing_results(): void
    {
        $strategy = $this->createTestStrategy();
        $strategy->startBacktest();

        $results = [
            'totalTrades' => 100,
            'winRate' => 60.0,
            'profitability' => 25.5,
            'maxDrawdown' => 15.0,
        ];

        $strategy->completeBacktest($results);

        $this->assertEquals(StrategyStatus::BACKTEST_PASSED, $strategy->getStatus());
        $this->assertEquals($results, $strategy->getBacktestResults());
        $this->assertNotNull($strategy->getLastBacktestedAt());
    }

    public function test_completes_backtest_with_failing_results(): void
    {
        $strategy = $this->createTestStrategy();
        $strategy->startBacktest();

        $results = [
            'totalTrades' => 50,
            'winRate' => 35.0, // Below 40%
            'profitability' => -10.0,
            'maxDrawdown' => 25.0, // Above max allowed (20%)
        ];

        $strategy->completeBacktest($results);

        $this->assertEquals(StrategyStatus::BACKTEST_FAILED, $strategy->getStatus());
    }

    public function test_activates_strategy_after_passing_backtest(): void
    {
        $strategy = $this->createTestStrategy();
        $strategy->startBacktest();
        $strategy->completeBacktest([
            'totalTrades' => 100,
            'winRate' => 55.0,
            'profitability' => 20.0,
            'maxDrawdown' => 10.0,
        ]);

        $strategy->activate();

        $this->assertEquals(StrategyStatus::ACTIVE, $strategy->getStatus());
        $this->assertNotNull($strategy->getActivatedAt());
    }

    public function test_cannot_activate_strategy_without_passed_backtest(): void
    {
        $strategy = $this->createTestStrategy();

        $this->expectException(\DomainException::class);

        $strategy->activate();
    }

    public function test_pauses_active_strategy(): void
    {
        $strategy = $this->createTestStrategy();
        $this->activateStrategy($strategy);

        $strategy->pause();

        $this->assertEquals(StrategyStatus::PAUSED, $strategy->getStatus());
    }

    public function test_stops_active_strategy(): void
    {
        $strategy = $this->createTestStrategy();
        $this->activateStrategy($strategy);

        $strategy->stop();

        $this->assertEquals(StrategyStatus::STOPPED, $strategy->getStatus());
        $this->assertNotNull($strategy->getStoppedAt());
    }

    public function test_updates_configuration_when_not_active(): void
    {
        $strategy = $this->createTestStrategy();

        $strategy->updateConfiguration(
            name: 'Updated Strategy',
            description: 'New description',
            symbols: ['BTCUSDT'],
            timeFrame: TimeFrame::FOUR_HOURS,
            indicators: [['indicator' => Indicator::EMA, 'parameters' => ['period' => 50]]],
            entryRules: [['field' => 'ema', 'operator' => 'crosses_above', 'value' => 0]],
            exitRules: [['field' => 'ema', 'operator' => 'crosses_below', 'value' => 0]],
            positionSizePercent: 5.0,
            maxDrawdownPercent: 15.0,
            stopLossPercent: 2.0,
            takeProfitPercent: 5.0
        );

        $this->assertEquals('Updated Strategy', $strategy->getName());
        $this->assertEquals(TimeFrame::FOUR_HOURS, $strategy->getTimeFrame());
        $this->assertEquals(5.0, $strategy->getPositionSizePercent());
    }

    public function test_cannot_update_configuration_when_active(): void
    {
        $strategy = $this->createTestStrategy();
        $this->activateStrategy($strategy);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Cannot update configuration of active strategy');

        $strategy->updateConfiguration(
            name: 'Updated Strategy',
            description: null,
            symbols: ['BTCUSDT'],
            timeFrame: TimeFrame::FOUR_HOURS,
            indicators: [['indicator' => Indicator::EMA, 'parameters' => ['period' => 50]]],
            entryRules: [['field' => 'ema', 'operator' => '>', 'value' => 0]],
            exitRules: [['field' => 'ema', 'operator' => '<', 'value' => 0]],
            positionSizePercent: 5.0,
            maxDrawdownPercent: 15.0,
            stopLossPercent: null,
            takeProfitPercent: null
        );
    }

    private function createTestStrategy(): TradingStrategy
    {
        return TradingStrategy::create(
            userId: UserId::generate(),
            name: 'Test Strategy',
            description: 'Test description',
            type: StrategyType::TREND_FOLLOWING,
            symbols: ['BTCUSDT'],
            timeFrame: TimeFrame::ONE_HOUR,
            indicators: [
                ['indicator' => Indicator::RSI, 'parameters' => ['period' => 14]],
            ],
            entryRules: [
                ['field' => 'rsi', 'operator' => '<', 'value' => 30],
            ],
            exitRules: [
                ['field' => 'rsi', 'operator' => '>', 'value' => 70],
            ],
            positionSizePercent: 10.0,
            maxDrawdownPercent: 20.0
        );
    }

    private function activateStrategy(TradingStrategy $strategy): void
    {
        $strategy->startBacktest();
        $strategy->completeBacktest([
            'totalTrades' => 100,
            'winRate' => 50.0,
            'profitability' => 15.0,
            'maxDrawdown' => 10.0,
        ]);
        $strategy->activate();
    }
}
