<?php

declare(strict_types=1);

namespace App\Trading\Domain\ValueObject;

enum OrderType: string
{
    case MARKET = 'market';
    case LIMIT = 'limit';
    case STOP_LOSS = 'stop_loss';
    case STOP_LOSS_LIMIT = 'stop_loss_limit';
    case TAKE_PROFIT = 'take_profit';
    case TAKE_PROFIT_LIMIT = 'take_profit_limit';
    case LIMIT_MAKER = 'limit_maker';

    public function getDisplayName(): string
    {
        return match($this) {
            self::MARKET => 'Market',
            self::LIMIT => 'Limit',
            self::STOP_LOSS => 'Stop Loss',
            self::STOP_LOSS_LIMIT => 'Stop Loss Limit',
            self::TAKE_PROFIT => 'Take Profit',
            self::TAKE_PROFIT_LIMIT => 'Take Profit Limit',
            self::LIMIT_MAKER => 'Limit Maker',
        };
    }

    public function requiresPrice(): bool
    {
        return match($this) {
            self::LIMIT, self::STOP_LOSS_LIMIT, self::TAKE_PROFIT_LIMIT, self::LIMIT_MAKER => true,
            self::MARKET, self::STOP_LOSS, self::TAKE_PROFIT => false,
        };
    }

    public function requiresStopPrice(): bool
    {
        return match($this) {
            self::STOP_LOSS, self::STOP_LOSS_LIMIT, self::TAKE_PROFIT, self::TAKE_PROFIT_LIMIT => true,
            default => false,
        };
    }

    /**
     * Convert to Binance API order type
     */
    public function toBinanceType(): string
    {
        return match($this) {
            self::MARKET => 'MARKET',
            self::LIMIT => 'LIMIT',
            self::STOP_LOSS => 'STOP_LOSS',
            self::STOP_LOSS_LIMIT => 'STOP_LOSS_LIMIT',
            self::TAKE_PROFIT => 'TAKE_PROFIT',
            self::TAKE_PROFIT_LIMIT => 'TAKE_PROFIT_LIMIT',
            self::LIMIT_MAKER => 'LIMIT_MAKER',
        };
    }
}
