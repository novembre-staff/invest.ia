<?php

declare(strict_types=1);

namespace App\Strategy\Domain\ValueObject;

enum StrategyStatus: string
{
    case DRAFT = 'draft';
    case BACKTESTING = 'backtesting';
    case BACKTEST_PASSED = 'backtest_passed';
    case BACKTEST_FAILED = 'backtest_failed';
    case ACTIVE = 'active';
    case PAUSED = 'paused';
    case STOPPED = 'stopped';

    public function isRunnable(): bool
    {
        return in_array($this, [self::BACKTEST_PASSED, self::ACTIVE, self::PAUSED], true);
    }

    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    public function canBeActivated(): bool
    {
        return in_array($this, [self::BACKTEST_PASSED, self::PAUSED], true);
    }

    public function canBePaused(): bool
    {
        return $this === self::ACTIVE;
    }

    public function canBeStopped(): bool
    {
        return in_array($this, [self::ACTIVE, self::PAUSED], true);
    }

    public function canBeBacktested(): bool
    {
        return in_array($this, [self::DRAFT, self::BACKTEST_FAILED], true);
    }
}
