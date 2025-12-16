<?php

declare(strict_types=1);

namespace App\Strategy\Domain\ValueObject;

enum Indicator: string
{
    case SMA = 'sma'; // Simple Moving Average
    case EMA = 'ema'; // Exponential Moving Average
    case RSI = 'rsi'; // Relative Strength Index
    case MACD = 'macd'; // Moving Average Convergence Divergence
    case BOLLINGER_BANDS = 'bollinger_bands';
    case STOCHASTIC = 'stochastic';
    case ATR = 'atr'; // Average True Range
    case ADX = 'adx'; // Average Directional Index
    case VWAP = 'vwap'; // Volume Weighted Average Price
    case FIBONACCI = 'fibonacci';

    public function getDescription(): string
    {
        return match ($this) {
            self::SMA => 'Simple Moving Average - Trend indicator',
            self::EMA => 'Exponential Moving Average - Weighted trend indicator',
            self::RSI => 'Relative Strength Index - Momentum oscillator (0-100)',
            self::MACD => 'MACD - Trend and momentum indicator',
            self::BOLLINGER_BANDS => 'Bollinger Bands - Volatility indicator',
            self::STOCHASTIC => 'Stochastic Oscillator - Momentum indicator',
            self::ATR => 'Average True Range - Volatility measure',
            self::ADX => 'Average Directional Index - Trend strength',
            self::VWAP => 'Volume Weighted Average Price - Average price by volume',
            self::FIBONACCI => 'Fibonacci Retracement - Support/resistance levels',
        };
    }

    public function getDefaultParameters(): array
    {
        return match ($this) {
            self::SMA, self::EMA => ['period' => 20],
            self::RSI => ['period' => 14],
            self::MACD => ['fastPeriod' => 12, 'slowPeriod' => 26, 'signalPeriod' => 9],
            self::BOLLINGER_BANDS => ['period' => 20, 'standardDeviations' => 2],
            self::STOCHASTIC => ['kPeriod' => 14, 'dPeriod' => 3],
            self::ATR => ['period' => 14],
            self::ADX => ['period' => 14],
            self::VWAP => [],
            self::FIBONACCI => ['levels' => [0.236, 0.382, 0.5, 0.618, 0.786]],
        };
    }

    public function isOscillator(): bool
    {
        return in_array($this, [self::RSI, self::STOCHASTIC], true);
    }

    public function isTrendIndicator(): bool
    {
        return in_array($this, [self::SMA, self::EMA, self::MACD, self::ADX], true);
    }

    public function isVolatilityIndicator(): bool
    {
        return in_array($this, [self::BOLLINGER_BANDS, self::ATR], true);
    }
}
