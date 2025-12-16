<?php

declare(strict_types=1);

namespace Tests\Risk\Infrastructure\Service;

use App\Risk\Infrastructure\Service\PortfolioRiskCalculator;
use PHPUnit\Framework\TestCase;

class PortfolioRiskCalculatorTest extends TestCase
{
    private PortfolioRiskCalculator $calculator;

    protected function setUp(): void
    {
        $portfolioProvider = $this->createMock(\App\Portfolio\Infrastructure\Adapter\PortfolioProviderInterface::class);
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);

        $this->calculator = new PortfolioRiskCalculator($portfolioProvider, $logger);
    }

    public function test_calculates_var_correctly(): void
    {
        $returns = [-5.0, -3.0, -1.0, 0.0, 1.0, 2.0, 3.0, 4.0, 5.0, 6.0];

        $var = $this->calculator->calculateVaR($returns, 0.95);

        // At 95% confidence, VaR should be around 5% (5th percentile)
        $this->assertGreaterThan(0, $var);
        $this->assertLessThanOrEqual(5.0, $var);
    }

    public function test_calculates_sharpe_ratio(): void
    {
        // Positive returns
        $returns = [0.01, 0.02, 0.015, 0.03, 0.025, 0.02, 0.018, 0.022];

        $sharpeRatio = $this->calculator->calculateSharpeRatio($returns, 0.02);

        // With positive returns and low risk-free rate, Sharpe should be positive
        $this->assertGreaterThan(0, $sharpeRatio);
    }

    public function test_calculates_sharpe_ratio_with_negative_returns(): void
    {
        // Negative returns
        $returns = [-0.01, -0.02, -0.015, -0.01, -0.02];

        $sharpeRatio = $this->calculator->calculateSharpeRatio($returns, 0.02);

        // With negative returns, Sharpe should be negative
        $this->assertLessThan(0, $sharpeRatio);
    }

    public function test_calculates_volatility(): void
    {
        $returns = [0.02, -0.01, 0.03, -0.02, 0.015, -0.005, 0.025];

        $volatility = $this->calculator->calculateVolatility($returns);

        $this->assertGreaterThan(0, $volatility);
        $this->assertLessThan(1, $volatility); // Annualized volatility should be reasonable
    }

    public function test_calculates_max_drawdown(): void
    {
        $equityCurve = [10000, 10500, 11000, 10200, 9800, 9500, 10000, 10800, 11500];

        $maxDrawdown = $this->calculator->calculateMaxDrawdown($equityCurve);

        // From peak of 11000 to trough of 9500 = 13.6% drawdown
        $this->assertGreaterThan(13.0, $maxDrawdown);
        $this->assertLessThan(14.0, $maxDrawdown);
    }

    public function test_calculates_max_drawdown_with_no_drawdown(): void
    {
        $equityCurve = [10000, 10500, 11000, 11500, 12000];

        $maxDrawdown = $this->calculator->calculateMaxDrawdown($equityCurve);

        $this->assertEquals(0.0, $maxDrawdown);
    }

    public function test_handles_empty_returns_for_var(): void
    {
        $var = $this->calculator->calculateVaR([], 0.95);
        $this->assertEquals(0.0, $var);
    }

    public function test_handles_empty_returns_for_sharpe(): void
    {
        $sharpe = $this->calculator->calculateSharpeRatio([], 0.02);
        $this->assertEquals(0.0, $sharpe);
    }

    public function test_handles_empty_returns_for_volatility(): void
    {
        $volatility = $this->calculator->calculateVolatility([]);
        $this->assertEquals(0.0, $volatility);
    }

    public function test_var_with_different_confidence_levels(): void
    {
        $returns = array_map(fn($x) => $x / 100, range(-10, 10));

        $var95 = $this->calculator->calculateVaR($returns, 0.95);
        $var99 = $this->calculator->calculateVaR($returns, 0.99);

        // VaR at 99% confidence should be higher than at 95%
        $this->assertGreaterThan($var95, $var99);
    }
}
