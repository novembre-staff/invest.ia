<?php

declare(strict_types=1);

namespace App\Strategy\Domain\ValueObject;

enum StrategyType: string
{
    case TREND_FOLLOWING = 'trend_following';
    case MEAN_REVERSION = 'mean_reversion';
    case BREAKOUT = 'breakout';
    case SCALPING = 'scalping';
    case SWING_TRADING = 'swing_trading';
    case DCA = 'dca'; // Dollar-Cost Averaging
    case GRID_TRADING = 'grid_trading';
    case CUSTOM = 'custom';

    public function getDescription(): string
    {
        return match ($this) {
            self::TREND_FOLLOWING => 'Follow market trends using moving averages and momentum',
            self::MEAN_REVERSION => 'Trade on price reversions to the mean',
            self::BREAKOUT => 'Enter positions on support/resistance breakouts',
            self::SCALPING => 'Multiple small profits on minor price changes',
            self::SWING_TRADING => 'Hold positions for several days to catch swings',
            self::DCA => 'Invest fixed amounts at regular intervals',
            self::GRID_TRADING => 'Place buy/sell orders at set intervals above/below price',
            self::CUSTOM => 'Custom strategy with user-defined rules',
        };
    }

    public function requiresIndicators(): bool
    {
        return match ($this) {
            self::TREND_FOLLOWING, self::MEAN_REVERSION, self::BREAKOUT, self::SCALPING, self::SWING_TRADING => true,
            self::DCA, self::GRID_TRADING, self::CUSTOM => false,
        };
    }
}
