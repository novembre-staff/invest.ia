<?php

declare(strict_types=1);

namespace App\Strategy\Infrastructure\Service;

use App\Strategy\Domain\Service\IndicatorCalculatorInterface;
use App\Strategy\Domain\ValueObject\Indicator;

class TechnicalIndicatorCalculator implements IndicatorCalculatorInterface
{
    public function calculate(Indicator $indicator, array $priceData, array $parameters): array
    {
        return match ($indicator) {
            Indicator::SMA => $this->calculateSMA($priceData, $parameters),
            Indicator::EMA => $this->calculateEMA($priceData, $parameters),
            Indicator::RSI => $this->calculateRSI($priceData, $parameters),
            Indicator::MACD => $this->calculateMACD($priceData, $parameters),
            Indicator::BOLLINGER_BANDS => $this->calculateBollingerBands($priceData, $parameters),
            Indicator::STOCHASTIC => $this->calculateStochastic($priceData, $parameters),
            Indicator::ATR => $this->calculateATR($priceData, $parameters),
            Indicator::ADX => $this->calculateADX($priceData, $parameters),
            Indicator::VWAP => $this->calculateVWAP($priceData, $parameters),
            default => throw new \InvalidArgumentException("Unsupported indicator: {$indicator->value}"),
        };
    }

    public function supports(Indicator $indicator): bool
    {
        return !in_array($indicator, [Indicator::FIBONACCI], true);
    }

    private function calculateSMA(array $priceData, array $parameters): array
    {
        $period = $parameters['period'] ?? 20;
        $closes = array_column($priceData, 'close');
        $sma = [];

        for ($i = $period - 1; $i < count($closes); $i++) {
            $sum = array_sum(array_slice($closes, $i - $period + 1, $period));
            $sma[] = [
                'time' => $priceData[$i]['time'],
                'value' => $sum / $period,
            ];
        }

        return $sma;
    }

    private function calculateEMA(array $priceData, array $parameters): array
    {
        $period = $parameters['period'] ?? 20;
        $closes = array_column($priceData, 'close');
        $multiplier = 2 / ($period + 1);
        $ema = [];

        // First EMA is SMA
        $sum = array_sum(array_slice($closes, 0, $period));
        $previousEma = $sum / $period;
        $ema[] = [
            'time' => $priceData[$period - 1]['time'],
            'value' => $previousEma,
        ];

        // Calculate subsequent EMAs
        for ($i = $period; $i < count($closes); $i++) {
            $currentEma = ($closes[$i] - $previousEma) * $multiplier + $previousEma;
            $ema[] = [
                'time' => $priceData[$i]['time'],
                'value' => $currentEma,
            ];
            $previousEma = $currentEma;
        }

        return $ema;
    }

    private function calculateRSI(array $priceData, array $parameters): array
    {
        $period = $parameters['period'] ?? 14;
        $closes = array_column($priceData, 'close');
        $rsi = [];

        if (count($closes) < $period + 1) {
            return [];
        }

        // Calculate price changes
        $gains = [];
        $losses = [];
        for ($i = 1; $i < count($closes); $i++) {
            $change = $closes[$i] - $closes[$i - 1];
            $gains[] = $change > 0 ? $change : 0;
            $losses[] = $change < 0 ? abs($change) : 0;
        }

        // Initial average gain/loss
        $avgGain = array_sum(array_slice($gains, 0, $period)) / $period;
        $avgLoss = array_sum(array_slice($losses, 0, $period)) / $period;

        // Calculate RSI
        for ($i = $period; $i < count($closes); $i++) {
            $currentGain = $gains[$i - 1];
            $currentLoss = $losses[$i - 1];

            $avgGain = (($avgGain * ($period - 1)) + $currentGain) / $period;
            $avgLoss = (($avgLoss * ($period - 1)) + $currentLoss) / $period;

            if ($avgLoss == 0) {
                $rsiValue = 100;
            } else {
                $rs = $avgGain / $avgLoss;
                $rsiValue = 100 - (100 / (1 + $rs));
            }

            $rsi[] = [
                'time' => $priceData[$i]['time'],
                'value' => $rsiValue,
            ];
        }

        return $rsi;
    }

    private function calculateMACD(array $priceData, array $parameters): array
    {
        $fastPeriod = $parameters['fastPeriod'] ?? 12;
        $slowPeriod = $parameters['slowPeriod'] ?? 26;
        $signalPeriod = $parameters['signalPeriod'] ?? 9;

        $fastEMA = $this->calculateEMA($priceData, ['period' => $fastPeriod]);
        $slowEMA = $this->calculateEMA($priceData, ['period' => $slowPeriod]);

        // Calculate MACD line (fast EMA - slow EMA)
        $macdLine = [];
        $minCount = min(count($fastEMA), count($slowEMA));
        for ($i = 0; $i < $minCount; $i++) {
            $macdLine[] = [
                'time' => $fastEMA[$i]['time'],
                'value' => $fastEMA[$i]['value'] - $slowEMA[$i]['value'],
            ];
        }

        // Calculate signal line (EMA of MACD line)
        $signalLine = [];
        $multiplier = 2 / ($signalPeriod + 1);
        
        if (count($macdLine) >= $signalPeriod) {
            $sum = 0;
            for ($i = 0; $i < $signalPeriod; $i++) {
                $sum += $macdLine[$i]['value'];
            }
            $previousSignal = $sum / $signalPeriod;
            $signalLine[] = [
                'time' => $macdLine[$signalPeriod - 1]['time'],
                'value' => $previousSignal,
            ];

            for ($i = $signalPeriod; $i < count($macdLine); $i++) {
                $currentSignal = ($macdLine[$i]['value'] - $previousSignal) * $multiplier + $previousSignal;
                $signalLine[] = [
                    'time' => $macdLine[$i]['time'],
                    'value' => $currentSignal,
                ];
                $previousSignal = $currentSignal;
            }
        }

        // Calculate histogram (MACD - Signal)
        $result = [];
        for ($i = 0; $i < count($signalLine); $i++) {
            $result[] = [
                'time' => $signalLine[$i]['time'],
                'macd' => $macdLine[$i + $signalPeriod - 1]['value'],
                'signal' => $signalLine[$i]['value'],
                'histogram' => $macdLine[$i + $signalPeriod - 1]['value'] - $signalLine[$i]['value'],
            ];
        }

        return $result;
    }

    private function calculateBollingerBands(array $priceData, array $parameters): array
    {
        $period = $parameters['period'] ?? 20;
        $stdDevMultiplier = $parameters['standardDeviations'] ?? 2;
        $closes = array_column($priceData, 'close');
        $bands = [];

        for ($i = $period - 1; $i < count($closes); $i++) {
            $slice = array_slice($closes, $i - $period + 1, $period);
            $sma = array_sum($slice) / $period;
            
            // Calculate standard deviation
            $variance = 0;
            foreach ($slice as $value) {
                $variance += pow($value - $sma, 2);
            }
            $stdDev = sqrt($variance / $period);

            $bands[] = [
                'time' => $priceData[$i]['time'],
                'upper' => $sma + ($stdDevMultiplier * $stdDev),
                'middle' => $sma,
                'lower' => $sma - ($stdDevMultiplier * $stdDev),
            ];
        }

        return $bands;
    }

    private function calculateStochastic(array $priceData, array $parameters): array
    {
        $kPeriod = $parameters['kPeriod'] ?? 14;
        $dPeriod = $parameters['dPeriod'] ?? 3;
        $stochastic = [];

        if (count($priceData) < $kPeriod) {
            return [];
        }

        $kValues = [];
        for ($i = $kPeriod - 1; $i < count($priceData); $i++) {
            $slice = array_slice($priceData, $i - $kPeriod + 1, $kPeriod);
            $highest = max(array_column($slice, 'high'));
            $lowest = min(array_column($slice, 'low'));
            $close = $priceData[$i]['close'];

            $k = $highest == $lowest ? 50 : (($close - $lowest) / ($highest - $lowest)) * 100;
            $kValues[] = [
                'time' => $priceData[$i]['time'],
                'value' => $k,
            ];
        }

        // Calculate %D (SMA of %K)
        for ($i = $dPeriod - 1; $i < count($kValues); $i++) {
            $slice = array_slice($kValues, $i - $dPeriod + 1, $dPeriod);
            $d = array_sum(array_column($slice, 'value')) / $dPeriod;

            $stochastic[] = [
                'time' => $kValues[$i]['time'],
                'k' => $kValues[$i]['value'],
                'd' => $d,
            ];
        }

        return $stochastic;
    }

    private function calculateATR(array $priceData, array $parameters): array
    {
        $period = $parameters['period'] ?? 14;
        $atr = [];

        if (count($priceData) < $period + 1) {
            return [];
        }

        $trueRanges = [];
        for ($i = 1; $i < count($priceData); $i++) {
            $high = $priceData[$i]['high'];
            $low = $priceData[$i]['low'];
            $previousClose = $priceData[$i - 1]['close'];

            $tr = max(
                $high - $low,
                abs($high - $previousClose),
                abs($low - $previousClose)
            );
            $trueRanges[] = $tr;
        }

        // Initial ATR is simple average
        $currentATR = array_sum(array_slice($trueRanges, 0, $period)) / $period;
        $atr[] = [
            'time' => $priceData[$period]['time'],
            'value' => $currentATR,
        ];

        // Subsequent ATRs use Wilder's smoothing
        for ($i = $period; $i < count($trueRanges); $i++) {
            $currentATR = (($currentATR * ($period - 1)) + $trueRanges[$i]) / $period;
            $atr[] = [
                'time' => $priceData[$i + 1]['time'],
                'value' => $currentATR,
            ];
        }

        return $atr;
    }

    private function calculateADX(array $priceData, array $parameters): array
    {
        $period = $parameters['period'] ?? 14;
        
        // ADX calculation is complex - simplified version
        // In production, use library like trader extension
        return [['time' => end($priceData)['time'], 'value' => 25]]; // Placeholder
    }

    private function calculateVWAP(array $priceData, array $parameters): array
    {
        $vwap = [];
        $cumulativeTPV = 0; // Typical Price * Volume
        $cumulativeVolume = 0;

        foreach ($priceData as $candle) {
            $typicalPrice = ($candle['high'] + $candle['low'] + $candle['close']) / 3;
            $cumulativeTPV += $typicalPrice * $candle['volume'];
            $cumulativeVolume += $candle['volume'];

            $vwap[] = [
                'time' => $candle['time'],
                'value' => $cumulativeVolume > 0 ? $cumulativeTPV / $cumulativeVolume : 0,
            ];
        }

        return $vwap;
    }
}
