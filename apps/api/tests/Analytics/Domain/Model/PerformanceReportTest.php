<?php

declare(strict_types=1);

namespace Tests\Analytics\Domain\Model;

use App\Analytics\Domain\Model\PerformanceReport;
use App\Analytics\Domain\ValueObject\AssetAllocation;
use App\Analytics\Domain\ValueObject\PerformanceMetrics;
use App\Analytics\Domain\ValueObject\ReportType;
use App\Analytics\Domain\ValueObject\TimePeriod;
use App\Identity\Domain\ValueObject\UserId;
use PHPUnit\Framework\TestCase;

class PerformanceReportTest extends TestCase
{
    public function test_creates_performance_report(): void
    {
        $report = PerformanceReport::create(
            userId: UserId::generate(),
            type: ReportType::PORTFOLIO_PERFORMANCE,
            period: TimePeriod::LAST_30_DAYS
        );

        $this->assertEquals(ReportType::PORTFOLIO_PERFORMANCE, $report->getType());
        $this->assertEquals(TimePeriod::LAST_30_DAYS, $report->getPeriod());
        $this->assertNotNull($report->getStartDate());
        $this->assertInstanceOf(\DateTimeImmutable::class, $report->getEndDate());
    }

    public function test_creates_report_with_custom_dates(): void
    {
        $startDate = new \DateTimeImmutable('2024-01-01');
        $endDate = new \DateTimeImmutable('2024-01-31');

        $report = PerformanceReport::create(
            userId: UserId::generate(),
            type: ReportType::TRADING_SUMMARY,
            period: TimePeriod::CUSTOM,
            startDate: $startDate,
            endDate: $endDate
        );

        $this->assertEquals($startDate, $report->getStartDate());
        $this->assertEquals($endDate, $report->getEndDate());
    }

    public function test_sets_performance_metrics(): void
    {
        $report = PerformanceReport::create(
            userId: UserId::generate(),
            type: ReportType::PORTFOLIO_PERFORMANCE,
            period: TimePeriod::LAST_30_DAYS
        );

        $metrics = new PerformanceMetrics(
            totalReturn: 1500.0,
            totalReturnPercent: 15.0,
            dailyReturn: 0.5,
            weeklyReturn: 3.0,
            monthlyReturn: 15.0,
            yearlyReturn: 180.0,
            sharpeRatio: 2.5,
            sortinoRatio: 3.0,
            maxDrawdown: 5.0,
            volatility: 15.0,
            winningTrades: 25,
            losingTrades: 10,
            winRate: 71.4,
            averageWin: 100.0,
            averageLoss: -50.0,
            profitFactor: 2.0
        );

        $report->setMetrics($metrics);

        $this->assertNotNull($report->getMetrics());
        $this->assertEquals(1500.0, $report->getMetrics()->getTotalReturn());
    }

    public function test_sets_asset_allocation(): void
    {
        $report = PerformanceReport::create(
            userId: UserId::generate(),
            type: ReportType::ASSET_ALLOCATION,
            period: TimePeriod::LAST_30_DAYS
        );

        $allocation = new AssetAllocation([
            'BTCUSDT' => 60.0,
            'ETHUSDT' => 40.0
        ], 10000.0);

        $report->setAllocation($allocation);

        $this->assertNotNull($report->getAllocation());
        $this->assertEquals(10000.0, $report->getAllocation()->getTotalValue());
    }

    public function test_adds_custom_data(): void
    {
        $report = PerformanceReport::create(
            userId: UserId::generate(),
            type: ReportType::TAX_REPORT,
            period: TimePeriod::LAST_YEAR
        );

        $report->addData('total_gains', 5000.0);
        $report->addData('total_losses', -1000.0);

        $data = $report->getData();
        $this->assertEquals(5000.0, $data['total_gains']);
        $this->assertEquals(-1000.0, $data['total_losses']);
    }

    public function test_calculates_duration_in_days(): void
    {
        $startDate = new \DateTimeImmutable('2024-01-01');
        $endDate = new \DateTimeImmutable('2024-01-31');

        $report = PerformanceReport::create(
            userId: UserId::generate(),
            type: ReportType::PROFIT_LOSS,
            period: TimePeriod::CUSTOM,
            startDate: $startDate,
            endDate: $endDate
        );

        $this->assertEquals(30, $report->getDurationInDays());
    }
}
