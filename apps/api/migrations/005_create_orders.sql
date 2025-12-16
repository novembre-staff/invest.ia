CREATE TABLE orders (
    id VARCHAR(36) PRIMARY KEY,
    user_id VARCHAR(36) NOT NULL,
    symbol VARCHAR(20) NOT NULL,
    type VARCHAR(30) NOT NULL,
    side VARCHAR(10) NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'pending',
    quantity DECIMAL(18, 8) NOT NULL,
    price DECIMAL(18, 8),
    stop_price DECIMAL(18, 8),
    time_in_force VARCHAR(10) NOT NULL DEFAULT 'GTC',
    executed_quantity DECIMAL(18, 8) NOT NULL DEFAULT 0,
    cumulative_quote_quantity DECIMAL(18, 8) NOT NULL DEFAULT 0,
    exchange_order_id VARCHAR(100),
    client_order_id VARCHAR(100),
    executed_at TIMESTAMP,
    reject_reason TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_orders_user_id ON orders(user_id);
CREATE INDEX idx_orders_symbol ON orders(symbol);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_orders_exchange_order_id ON orders(exchange_order_id);
CREATE INDEX idx_orders_created_at ON orders(created_at DESC);
CREATE INDEX idx_orders_user_symbol ON orders(user_id, symbol);

-- Composite index for efficient active order queries
CREATE INDEX idx_orders_active ON orders(user_id, status) WHERE status IN ('pending', 'new', 'partially_filled');

-- Comment for documentation
COMMENT ON TABLE orders IS 'User trading orders executed on connected exchanges';
COMMENT ON COLUMN orders.type IS 'Order type: market, limit, stop_loss, stop_loss_limit, take_profit, take_profit_limit, limit_maker';
COMMENT ON COLUMN orders.side IS 'Order side: buy, sell';
COMMENT ON COLUMN orders.status IS 'Order status: pending, new, partially_filled, filled, cancelled, rejected, expired';
COMMENT ON COLUMN orders.time_in_force IS 'Time in force: GTC (Good Till Cancel), IOC (Immediate Or Cancel), FOK (Fill Or Kill)';
COMMENT ON COLUMN orders.exchange_order_id IS 'Order ID from the exchange (e.g., Binance order ID)';
COMMENT ON COLUMN orders.client_order_id IS 'Client order ID sent to exchange (usually our internal order ID)';
