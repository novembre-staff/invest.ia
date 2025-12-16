<?php

declare(strict_types=1);

namespace App\Alert\Domain\ValueObject;

enum AlertType: string
{
    case PRICE_ABOVE = 'price_above';
    case PRICE_BELOW = 'price_below';
    case PRICE_CHANGE_PERCENT = 'price_change_percent';
    case VOLUME_SPIKE = 'volume_spike';
    case PORTFOLIO_VALUE = 'portfolio_value';
    case POSITION_PROFIT_TARGET = 'position_profit_target';
    case POSITION_STOP_LOSS = 'position_stop_loss';

    public function getDisplayName(): string
    {
        return match($this) {
            self::PRICE_ABOVE => 'Price Above',
            self::PRICE_BELOW => 'Price Below',
            self::PRICE_CHANGE_PERCENT => 'Price Change %',
            self::VOLUME_SPIKE => 'Volume Spike',
            self::PORTFOLIO_VALUE => 'Portfolio Value',
            self::POSITION_PROFIT_TARGET => 'Take Profit',
            self::POSITION_STOP_LOSS => 'Stop Loss',
        };
    }

    public function requiresSymbol(): bool
    {
        return match($this) {
            self::PRICE_ABOVE,
            self::PRICE_BELOW,
            self::PRICE_CHANGE_PERCENT,
            self::VOLUME_SPIKE,
            self::POSITION_PROFIT_TARGET,
            self::POSITION_STOP_LOSS => true,
            self::PORTFOLIO_VALUE => false,
        };
    }
}
