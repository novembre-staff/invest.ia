<?php

declare(strict_types=1);

namespace Tests\Analytics\Domain\ValueObject;

use App\Analytics\Domain\ValueObject\AssetAllocation;
use PHPUnit\Framework\TestCase;

class AssetAllocationTest extends TestCase
{
    public function test_creates_valid_allocation(): void
    {
        $allocations = [
            'BTCUSDT' => 40.0,
            'ETHUSDT' => 30.0,
            'BNBUSDT' => 20.0,
            'ADAUSDT' => 10.0
        ];

        $allocation = new AssetAllocation($allocations, 10000.0);

        $this->assertEquals($allocations, $allocation->getAllocations());
        $this->assertEquals(10000.0, $allocation->getTotalValue());
    }

    public function test_validates_allocation_sum(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('must sum to 100%');

        new AssetAllocation([
            'BTCUSDT' => 40.0,
            'ETHUSDT' => 30.0,
            'BNBUSDT' => 20.0
        ], 10000.0);
    }

    public function test_validates_allocation_percentages(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new AssetAllocation([
            'BTCUSDT' => 150.0,
            'ETHUSDT' => -50.0
        ], 10000.0);
    }

    public function test_gets_allocation_for_symbol(): void
    {
        $allocation = new AssetAllocation([
            'BTCUSDT' => 60.0,
            'ETHUSDT' => 40.0
        ], 10000.0);

        $this->assertEquals(60.0, $allocation->getAllocation('BTCUSDT'));
        $this->assertEquals(40.0, $allocation->getAllocation('ETHUSDT'));
        $this->assertEquals(0.0, $allocation->getAllocation('BNBUSDT'));
    }

    public function test_calculates_value_for_symbol(): void
    {
        $allocation = new AssetAllocation([
            'BTCUSDT' => 60.0,
            'ETHUSDT' => 40.0
        ], 10000.0);

        $this->assertEquals(6000.0, $allocation->getValue('BTCUSDT'));
        $this->assertEquals(4000.0, $allocation->getValue('ETHUSDT'));
    }

    public function test_gets_top_assets(): void
    {
        $allocation = new AssetAllocation([
            'BTCUSDT' => 40.0,
            'ETHUSDT' => 30.0,
            'BNBUSDT' => 15.0,
            'ADAUSDT' => 10.0,
            'DOTUSDT' => 5.0
        ], 10000.0);

        $top3 = $allocation->getTopAssets(3);

        $this->assertCount(3, $top3);
        $this->assertEquals(['BTCUSDT' => 40.0, 'ETHUSDT' => 30.0, 'BNBUSDT' => 15.0], $top3);
    }

    public function test_calculates_diversification_score(): void
    {
        // Perfectly diversified (4 assets at 25% each)
        $balanced = new AssetAllocation([
            'BTCUSDT' => 25.0,
            'ETHUSDT' => 25.0,
            'BNBUSDT' => 25.0,
            'ADAUSDT' => 25.0
        ], 10000.0);

        // Concentrated (90% in one asset)
        $concentrated = new AssetAllocation([
            'BTCUSDT' => 90.0,
            'ETHUSDT' => 10.0
        ], 10000.0);

        $balancedScore = $balanced->getDiversificationScore();
        $concentratedScore = $concentrated->getDiversificationScore();

        $this->assertGreaterThan($concentratedScore, $balancedScore);
        $this->assertGreaterThan(0, $concentratedScore);
        $this->assertLessThanOrEqual(100, $balancedScore);
    }
}
