-- Migration 007: Create risk_profiles table
-- Sprint 7: Risk Management bounded context (UC-038 to UC-044)

CREATE TABLE risk_profiles (
    id VARCHAR(36) PRIMARY KEY,
    user_id VARCHAR(36) NOT NULL UNIQUE,
    risk_level VARCHAR(50) NOT NULL,
    max_position_size_percent REAL,
    max_portfolio_exposure_percent REAL,
    max_daily_loss_percent REAL,
    max_drawdown_percent REAL,
    max_leverage REAL,
    max_concentration_percent REAL,
    max_trades_per_day INTEGER,
    require_approval_above_limit BOOLEAN NOT NULL DEFAULT TRUE,
    notes TEXT,
    created_at TIMESTAMP NOT NULL,
    updated_at TIMESTAMP NOT NULL
);

-- Indexes
CREATE INDEX idx_risk_profiles_user_id ON risk_profiles(user_id);
CREATE INDEX idx_risk_profiles_risk_level ON risk_profiles(risk_level);

-- Column comments
COMMENT ON COLUMN risk_profiles.risk_level IS 'Risk tolerance level: very_low, low, moderate, high, very_high';
COMMENT ON COLUMN risk_profiles.max_position_size_percent IS 'Maximum percentage of portfolio for single position';
COMMENT ON COLUMN risk_profiles.max_portfolio_exposure_percent IS 'Maximum total portfolio exposure percentage';
COMMENT ON COLUMN risk_profiles.max_daily_loss_percent IS 'Maximum allowed daily loss percentage';
COMMENT ON COLUMN risk_profiles.max_drawdown_percent IS 'Maximum allowed drawdown from peak';
COMMENT ON COLUMN risk_profiles.max_leverage IS 'Maximum allowed leverage ratio';
COMMENT ON COLUMN risk_profiles.max_concentration_percent IS 'Maximum concentration in single asset';
COMMENT ON COLUMN risk_profiles.max_trades_per_day IS 'Maximum number of trades per day';
