-- Migration: 008 - Create automations table

CREATE TABLE automations (
    id VARCHAR(36) PRIMARY KEY,
    user_id VARCHAR(36) NOT NULL,
    name VARCHAR(255) NOT NULL,
    type VARCHAR(50) NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'draft',
    symbol VARCHAR(20) NOT NULL,
    interval VARCHAR(10),
    dca_config JSONB,
    grid_config JSONB,
    parameters JSONB DEFAULT '{}',
    execution_count INTEGER NOT NULL DEFAULT 0,
    total_invested REAL NOT NULL DEFAULT 0,
    total_profit REAL NOT NULL DEFAULT 0,
    last_executed_at TIMESTAMP,
    next_execution_at TIMESTAMP,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Indexes
CREATE INDEX idx_automations_user_id ON automations(user_id);
CREATE INDEX idx_automations_status ON automations(status);
CREATE INDEX idx_automations_type ON automations(type);
CREATE INDEX idx_automations_next_execution ON automations(next_execution_at);

-- Comments
COMMENT ON TABLE automations IS 'Trading automations: DCA, Grid Trading, Rebalancing, etc.';
COMMENT ON COLUMN automations.type IS 'Automation type: dca, grid_trading, rebalancing, stop_loss_trailing, take_profit_ladder, arbitrage, custom_script';
COMMENT ON COLUMN automations.status IS 'Automation status: draft, active, paused, stopped, completed, failed';
COMMENT ON COLUMN automations.interval IS 'Execution interval: 1m, 5m, 15m, 30m, 1h, 4h, 8h, 1d, 1w, 1M';
COMMENT ON COLUMN automations.dca_config IS 'DCA configuration (amount_per_purchase, max_total_investment, max_executions, end_date)';
COMMENT ON COLUMN automations.grid_config IS 'Grid trading configuration (lower_price, upper_price, grid_levels, quantity_per_grid, is_arithmetic)';
COMMENT ON COLUMN automations.parameters IS 'Additional automation-specific parameters';
COMMENT ON COLUMN automations.execution_count IS 'Number of times automation has been executed';
COMMENT ON COLUMN automations.total_invested IS 'Total amount invested through this automation';
COMMENT ON COLUMN automations.total_profit IS 'Total profit/loss from this automation';
COMMENT ON COLUMN automations.last_executed_at IS 'Last execution timestamp';
COMMENT ON COLUMN automations.next_execution_at IS 'Next scheduled execution timestamp';
