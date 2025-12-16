<?php

declare(strict_types=1);

namespace Tests\Strategy\Infrastructure\Service;

use App\Strategy\Domain\ValueObject\Indicator;
use App\Strategy\Infrastructure\Service\TechnicalIndicatorCalculator;
use PHPUnit\Framework\TestCase;

class TechnicalIndicatorCalculatorTest extends TestCase
{
    private TechnicalIndicatorCalculator $calculator;

    protected function setUp(): void
    {
        $this->calculator = new TechnicalIndicatorCalculator();
    }

    public function test_calculates_sma_correctly(): void
    {
        $priceData = $this->generatePriceData([100, 102, 104, 106, 108, 110]);

        $result = $this->calculator->calculate(
            Indicator::SMA,
            $priceData,
            ['period' => 3]
        );

        $this->assertCount(4, $result);
        $this->assertEquals(102.0, $result[0]['value']); // (100+102+104)/3
        $this->assertEquals(104.0, $result[1]['value']); // (102+104+106)/3
        $this->assertEquals(106.0, $result[2]['value']); // (104+106+108)/3
        $this->assertEquals(108.0, $result[3]['value']); // (106+108+110)/3
    }

    public function test_calculates_ema_correctly(): void
    {
        $priceData = $this->generatePriceData([100, 102, 104, 106, 108]);

        $result = $this->calculator->calculate(
            Indicator::EMA,
            $priceData,
            ['period' => 3]
        );

        $this->assertCount(3, $result);
        $this->assertEquals(102.0, $result[0]['value']); // First EMA is SMA
        // Subsequent EMAs use multiplier
        $this->assertGreaterThan(102.0, $result[1]['value']);
    }

    public function test_calculates_rsi_in_valid_range(): void
    {
        $prices = [44, 44.34, 44.09, 43.61, 44.33, 44.83, 45.10, 45.42, 45.84, 46.08, 
                   45.89, 46.03, 45.61, 46.28, 46.28, 46.00, 46.03, 46.41, 46.22, 45.64];
        $priceData = $this->generatePriceData($prices);

        $result = $this->calculator->calculate(
            Indicator::RSI,
            $priceData,
            ['period' => 14]
        );

        $this->assertNotEmpty($result);
        
        foreach ($result as $value) {
            $this->assertGreaterThanOrEqual(0, $value['value']);
            $this->assertLessThanOrEqual(100, $value['value']);
        }
    }

    public function test_calculates_macd_with_all_components(): void
    {
        $prices = range(100, 150, 1); // Trending prices
        $priceData = $this->generatePriceData($prices);

        $result = $this->calculator->calculate(
            Indicator::MACD,
            $priceData,
            ['fastPeriod' => 12, 'slowPeriod' => 26, 'signalPeriod' => 9]
        );

        $this->assertNotEmpty($result);
        
        foreach ($result as $value) {
            $this->assertArrayHasKey('macd', $value);
            $this->assertArrayHasKey('signal', $value);
            $this->assertArrayHasKey('histogram', $value);
        }
    }

    public function test_calculates_bollinger_bands_with_three_bands(): void
    {
        $prices = [100, 102, 101, 103, 105, 104, 106, 108, 107, 109, 110, 112, 111, 113, 115,
                   114, 116, 118, 117, 119, 120];
        $priceData = $this->generatePriceData($prices);

        $result = $this->calculator->calculate(
            Indicator::BOLLINGER_BANDS,
            $priceData,
            ['period' => 20, 'standardDeviations' => 2]
        );

        $this->assertCount(2, $result); // 21 candles, period 20, so 2 results

        foreach ($result as $value) {
            $this->assertArrayHasKey('upper', $value);
            $this->assertArrayHasKey('middle', $value);
            $this->assertArrayHasKey('lower', $value);
            
            // Upper should be above middle, middle above lower
            $this->assertGreaterThan($value['middle'], $value['upper']);
            $this->assertGreaterThan($value['lower'], $value['middle']);
        }
    }

    public function test_calculates_stochastic_in_valid_range(): void
    {
        $priceData = [];
        for ($i = 0; $i < 20; $i++) {
            $priceData[] = [
                'time' => time() + $i * 3600,
                'open' => 100 + $i,
                'high' => 105 + $i,
                'low' => 95 + $i,
                'close' => 100 + $i,
                'volume' => 1000,
            ];
        }

        $result = $this->calculator->calculate(
            Indicator::STOCHASTIC,
            $priceData,
            ['kPeriod' => 14, 'dPeriod' => 3]
        );

        $this->assertNotEmpty($result);

        foreach ($result as $value) {
            $this->assertArrayHasKey('k', $value);
            $this->assertArrayHasKey('d', $value);
            $this->assertGreaterThanOrEqual(0, $value['k']);
            $this->assertLessThanOrEqual(100, $value['k']);
            $this->assertGreaterThanOrEqual(0, $value['d']);
            $this->assertLessThanOrEqual(100, $value['d']);
        }
    }

    public function test_calculates_atr_with_positive_values(): void
    {
        $priceData = [];
        for ($i = 0; $i < 20; $i++) {
            $priceData[] = [
                'time' => time() + $i * 3600,
                'open' => 100,
                'high' => 105 + ($i % 3),
                'low' => 95 - ($i % 2),
                'close' => 100 + ($i % 5),
                'volume' => 1000,
            ];
        }

        $result = $this->calculator->calculate(
            Indicator::ATR,
            $priceData,
            ['period' => 14]
        );

        $this->assertNotEmpty($result);

        foreach ($result as $value) {
            $this->assertGreaterThan(0, $value['value']);
        }
    }

    public function test_calculates_vwap_weighted_by_volume(): void
    {
        $priceData = [
            ['time' => 1, 'open' => 100, 'high' => 102, 'low' => 98, 'close' => 100, 'volume' => 1000],
            ['time' => 2, 'open' => 100, 'high' => 104, 'low' => 99, 'close' => 102, 'volume' => 2000],
            ['time' => 3, 'open' => 102, 'high' => 106, 'low' => 101, 'close' => 105, 'volume' => 1500],
        ];

        $result = $this->calculator->calculate(
            Indicator::VWAP,
            $priceData,
            []
        );

        $this->assertCount(3, $result);

        // VWAP should be cumulative and volume-weighted
        $this->assertGreaterThan(0, $result[0]['value']);
        $this->assertGreaterThan($result[0]['value'], $result[2]['value']); // Should trend up with prices
    }

    public function test_supports_all_implemented_indicators(): void
    {
        $this->assertTrue($this->calculator->supports(Indicator::SMA));
        $this->assertTrue($this->calculator->supports(Indicator::EMA));
        $this->assertTrue($this->calculator->supports(Indicator::RSI));
        $this->assertTrue($this->calculator->supports(Indicator::MACD));
        $this->assertTrue($this->calculator->supports(Indicator::BOLLINGER_BANDS));
        $this->assertTrue($this->calculator->supports(Indicator::STOCHASTIC));
        $this->assertTrue($this->calculator->supports(Indicator::ATR));
        $this->assertTrue($this->calculator->supports(Indicator::VWAP));
        $this->assertFalse($this->calculator->supports(Indicator::FIBONACCI));
    }

    private function generatePriceData(array $closes): array
    {
        $data = [];
        foreach ($closes as $i => $close) {
            $data[] = [
                'time' => time() + $i * 3600,
                'open' => $close,
                'high' => $close + 1,
                'low' => $close - 1,
                'close' => $close,
                'volume' => 1000,
            ];
        }
        return $data;
    }
}
