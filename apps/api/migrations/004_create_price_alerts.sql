CREATE TABLE price_alerts (
    id VARCHAR(36) PRIMARY KEY,
    user_id VARCHAR(36) NOT NULL,
    type VARCHAR(50) NOT NULL,
    symbol VARCHAR(20),
    condition JSONB NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    notification_channels JSONB NOT NULL,
    message TEXT,
    triggered_at TIMESTAMP,
    expires_at TIMESTAMP,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_alerts_user_id ON price_alerts(user_id);
CREATE INDEX idx_alerts_status ON price_alerts(status);
CREATE INDEX idx_alerts_symbol ON price_alerts(symbol);
CREATE INDEX idx_alerts_type ON price_alerts(type);
CREATE INDEX idx_alerts_expires_at ON price_alerts(expires_at);

-- Composite index for efficient active alert queries
CREATE INDEX idx_alerts_active_symbol ON price_alerts(symbol, status) WHERE status = 'active';

-- Comment for documentation
COMMENT ON TABLE price_alerts IS 'User-created price alerts with configurable conditions and notification channels';
COMMENT ON COLUMN price_alerts.type IS 'Alert type: price_above, price_below, price_change_percent, volume_spike, portfolio_value, position_profit_target, position_stop_loss';
COMMENT ON COLUMN price_alerts.condition IS 'JSON object with targetValue, comparisonValue, and timeframeMinutes fields';
COMMENT ON COLUMN price_alerts.status IS 'Alert status: active, triggered, cancelled, expired';
COMMENT ON COLUMN price_alerts.notification_channels IS 'Array of channels: email, push, in_app, sms';
