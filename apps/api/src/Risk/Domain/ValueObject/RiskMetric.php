<?php

declare(strict_types=1);

namespace App\Risk\Domain\ValueObject;

enum RiskMetric: string
{
    case VALUE_AT_RISK = 'value_at_risk'; // VaR
    case SHARPE_RATIO = 'sharpe_ratio';
    case SORTINO_RATIO = 'sortino_ratio';
    case MAX_DRAWDOWN = 'max_drawdown';
    case BETA = 'beta';
    case VOLATILITY = 'volatility';
    case CORRELATION = 'correlation';

    public function getDescription(): string
    {
        return match ($this) {
            self::VALUE_AT_RISK => 'Value at Risk - Maximum expected loss over time period',
            self::SHARPE_RATIO => 'Sharpe Ratio - Risk-adjusted return',
            self::SORTINO_RATIO => 'Sortino Ratio - Downside risk-adjusted return',
            self::MAX_DRAWDOWN => 'Maximum Drawdown - Largest peak-to-trough decline',
            self::BETA => 'Beta - Volatility relative to market',
            self::VOLATILITY => 'Volatility - Standard deviation of returns',
            self::CORRELATION => 'Correlation - Relationship to market',
        };
    }

    public function requiresMarketData(): bool
    {
        return in_array($this, [self::BETA, self::CORRELATION], true);
    }
}
