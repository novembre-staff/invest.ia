-- Migration 006: Create trading_strategies table
-- Sprint 6: Strategy bounded context (UC-031 to UC-037)

CREATE TABLE trading_strategies (
    id VARCHAR(36) PRIMARY KEY,
    user_id VARCHAR(36) NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    type VARCHAR(50) NOT NULL,
    status VARCHAR(50) NOT NULL,
    symbols JSON NOT NULL,
    time_frame VARCHAR(10) NOT NULL,
    indicators JSON NOT NULL,
    entry_rules JSON NOT NULL,
    exit_rules JSON NOT NULL,
    position_size_percent REAL,
    max_drawdown_percent REAL,
    stop_loss_percent REAL,
    take_profit_percent REAL,
    backtest_results JSON,
    last_backtested_at TIMESTAMP,
    activated_at TIMESTAMP,
    stopped_at TIMESTAMP,
    created_at TIMESTAMP NOT NULL,
    updated_at TIMESTAMP NOT NULL
);

-- Indexes for performance
CREATE INDEX idx_trading_strategies_user_id ON trading_strategies(user_id);
CREATE INDEX idx_trading_strategies_status ON trading_strategies(status);
CREATE INDEX idx_trading_strategies_type ON trading_strategies(type);
CREATE INDEX idx_trading_strategies_created_at ON trading_strategies(created_at);

-- Composite index for common queries
CREATE INDEX idx_trading_strategies_user_status ON trading_strategies(user_id, status);

-- Partial index for active strategies (used by AutoTrader)
CREATE INDEX idx_trading_strategies_active ON trading_strategies(status) WHERE status = 'active';

-- Column comments
COMMENT ON COLUMN trading_strategies.type IS 'Strategy type: trend_following, mean_reversion, breakout, scalping, swing_trading, dca, grid_trading, custom';
COMMENT ON COLUMN trading_strategies.status IS 'Strategy status: draft, backtesting, backtest_passed, backtest_failed, active, paused, stopped';
COMMENT ON COLUMN trading_strategies.time_frame IS 'Timeframe: 1m, 5m, 15m, 30m, 1h, 4h, 1d, 1w, 1M';
COMMENT ON COLUMN trading_strategies.symbols IS 'JSON array of trading pairs: ["BTCUSDT", "ETHUSDT"]';
COMMENT ON COLUMN trading_strategies.indicators IS 'JSON array of indicators with parameters: [{"indicator": "rsi", "parameters": {"period": 14}}]';
COMMENT ON COLUMN trading_strategies.entry_rules IS 'JSON array of entry conditions: [{"field": "rsi", "operator": "<", "value": 30}]';
COMMENT ON COLUMN trading_strategies.exit_rules IS 'JSON array of exit conditions';
COMMENT ON COLUMN trading_strategies.backtest_results IS 'JSON object with backtest metrics: totalTrades, winRate, profitability, maxDrawdown, etc.';
