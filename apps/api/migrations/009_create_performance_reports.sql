-- Migration: 009 - Create performance_reports table

CREATE TABLE performance_reports (
    id VARCHAR(36) PRIMARY KEY,
    user_id VARCHAR(36) NOT NULL,
    type VARCHAR(50) NOT NULL,
    period VARCHAR(20) NOT NULL,
    start_date TIMESTAMP,
    end_date TIMESTAMP NOT NULL,
    metrics JSONB,
    allocation JSONB,
    data JSONB DEFAULT '{}',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Indexes
CREATE INDEX idx_performance_reports_user_id ON performance_reports(user_id);
CREATE INDEX idx_performance_reports_type ON performance_reports(type);
CREATE INDEX idx_performance_reports_created_at ON performance_reports(created_at);

-- Comments
COMMENT ON TABLE performance_reports IS 'Performance reports and analytics for user portfolios';
COMMENT ON COLUMN performance_reports.type IS 'Report type: portfolio_performance, trading_summary, asset_allocation, profit_loss, risk_analysis, tax_report';
COMMENT ON COLUMN performance_reports.period IS 'Time period: 24h, 7d, 30d, 90d, 6m, 1y, all, custom';
COMMENT ON COLUMN performance_reports.metrics IS 'Performance metrics (total_return, sharpe_ratio, max_drawdown, win_rate, etc.)';
COMMENT ON COLUMN performance_reports.allocation IS 'Asset allocation data (percentages, diversification score)';
COMMENT ON COLUMN performance_reports.data IS 'Additional report-specific data';
