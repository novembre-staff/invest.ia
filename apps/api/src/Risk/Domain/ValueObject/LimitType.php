<?php

declare(strict_types=1);

namespace App\Risk\Domain\ValueObject;

enum LimitType: string
{
    case MAX_POSITION_SIZE = 'max_position_size';
    case MAX_PORTFOLIO_EXPOSURE = 'max_portfolio_exposure';
    case MAX_DAILY_LOSS = 'max_daily_loss';
    case MAX_DRAWDOWN = 'max_drawdown';
    case MAX_LEVERAGE = 'max_leverage';
    case MAX_CONCENTRATION = 'max_concentration'; // Per asset
    case MAX_TRADES_PER_DAY = 'max_trades_per_day';

    public function getDescription(): string
    {
        return match ($this) {
            self::MAX_POSITION_SIZE => 'Maximum size for a single position',
            self::MAX_PORTFOLIO_EXPOSURE => 'Maximum total portfolio exposure',
            self::MAX_DAILY_LOSS => 'Maximum allowed loss per day',
            self::MAX_DRAWDOWN => 'Maximum allowed drawdown from peak',
            self::MAX_LEVERAGE => 'Maximum leverage ratio',
            self::MAX_CONCENTRATION => 'Maximum concentration in single asset',
            self::MAX_TRADES_PER_DAY => 'Maximum number of trades per day',
        };
    }

    public function isPercentage(): bool
    {
        return in_array($this, [
            self::MAX_POSITION_SIZE,
            self::MAX_PORTFOLIO_EXPOSURE,
            self::MAX_DAILY_LOSS,
            self::MAX_DRAWDOWN,
            self::MAX_CONCENTRATION,
        ], true);
    }

    public function isCount(): bool
    {
        return $this === self::MAX_TRADES_PER_DAY;
    }
}
