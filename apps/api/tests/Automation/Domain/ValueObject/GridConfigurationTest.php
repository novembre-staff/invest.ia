<?php

declare(strict_types=1);

namespace Tests\Automation\Domain\ValueObject;

use App\Automation\Domain\ValueObject\GridConfiguration;
use PHPUnit\Framework\TestCase;

class GridConfigurationTest extends TestCase
{
    public function test_creates_valid_grid_configuration(): void
    {
        $config = new GridConfiguration(
            lowerPrice: 40000.0,
            upperPrice: 50000.0,
            gridLevels: 10,
            quantityPerGrid: 0.01
        );

        $this->assertEquals(40000.0, $config->getLowerPrice());
        $this->assertEquals(50000.0, $config->getUpperPrice());
        $this->assertEquals(10, $config->getGridLevels());
        $this->assertEquals(0.01, $config->getQuantityPerGrid());
    }

    public function test_validates_lower_price_positive(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new GridConfiguration(
            lowerPrice: -100.0,
            upperPrice: 1000.0,
            gridLevels: 10,
            quantityPerGrid: 0.01
        );
    }

    public function test_validates_upper_greater_than_lower(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new GridConfiguration(
            lowerPrice: 50000.0,
            upperPrice: 40000.0,
            gridLevels: 10,
            quantityPerGrid: 0.01
        );
    }

    public function test_validates_minimum_grid_levels(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new GridConfiguration(
            lowerPrice: 40000.0,
            upperPrice: 50000.0,
            gridLevels: 1,
            quantityPerGrid: 0.01
        );
    }

    public function test_validates_maximum_grid_levels(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new GridConfiguration(
            lowerPrice: 40000.0,
            upperPrice: 50000.0,
            gridLevels: 101,
            quantityPerGrid: 0.01
        );
    }

    public function test_calculates_arithmetic_grid_prices(): void
    {
        $config = new GridConfiguration(
            lowerPrice: 40000.0,
            upperPrice: 50000.0,
            gridLevels: 5,
            quantityPerGrid: 0.01,
            isArithmetic: true
        );

        $prices = $config->calculateGridPrices();

        $this->assertCount(5, $prices);
        $this->assertEquals(40000.0, $prices[0]);
        $this->assertEquals(42500.0, $prices[1]);
        $this->assertEquals(45000.0, $prices[2]);
        $this->assertEquals(47500.0, $prices[3]);
        $this->assertEquals(50000.0, $prices[4]);
    }

    public function test_calculates_geometric_grid_prices(): void
    {
        $config = new GridConfiguration(
            lowerPrice: 40000.0,
            upperPrice: 50000.0,
            gridLevels: 5,
            quantityPerGrid: 0.01,
            isArithmetic: false
        );

        $prices = $config->calculateGridPrices();

        $this->assertCount(5, $prices);
        $this->assertEquals(40000.0, $prices[0]);
        $this->assertGreaterThan(40000.0, $prices[1]);
        $this->assertLessThan(50000.0, $prices[3]);
        $this->assertEquals(50000.0, round($prices[4], 2));
    }

    public function test_calculates_total_investment(): void
    {
        $config = new GridConfiguration(
            lowerPrice: 40000.0,
            upperPrice: 50000.0,
            gridLevels: 10,
            quantityPerGrid: 0.01
        );

        $totalInvestment = $config->getTotalInvestment();

        $this->assertEquals(0.1, $totalInvestment); // 10 * 0.01
    }
}
