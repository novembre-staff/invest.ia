# Event Model - Domain Events

Documentation des événements métier de la plateforme.

---

## Principes

- **Événements au passé** : `UserRegistered`, pas `RegisterUser`
- **Immutables** : les événements ne peuvent pas être modifiés
- **Auditables** : tous les événements sont loggés
- **Asynchrones** : propagation via Symfony Messenger
- **Découplage** : les bounded contexts communiquent via événements

---

## Identity Context

### UserRegistered
```json
{
  "eventId": "uuid",
  "eventType": "user.registered",
  "occurredAt": "2025-12-16T10:30:00Z",
  "userId": "uuid",
  "email": "user@example.com",
  "registeredAt": "2025-12-16T10:30:00Z"
}
```

### UserEmailVerified
### MfaEnabled
### MfaDisabled
### UserLoggedIn
### UserLoggedOut
### PasswordChanged
### SessionExpired

---

## Market Context

### AssetRegistered
```json
{
  "eventId": "uuid",
  "eventType": "asset.registered",
  "occurredAt": "2025-12-16T10:30:00Z",
  "symbol": "BTCUSDT",
  "baseAsset": "BTC",
  "quoteAsset": "USDT",
  "assetType": "crypto"
}
```

### PriceUpdated
```json
{
  "eventId": "uuid",
  "eventType": "price.updated",
  "occurredAt": "2025-12-16T10:30:00Z",
  "symbol": "BTCUSDT",
  "price": "45000.00",
  "bid": "44999.00",
  "ask": "45001.00",
  "volume24h": "1234567890",
  "source": "binance"
}
```

### SignificantPriceMove
```json
{
  "eventId": "uuid",
  "eventType": "price.significant_move",
  "occurredAt": "2025-12-16T10:30:00Z",
  "symbol": "BTCUSDT",
  "previousPrice": "45000.00",
  "newPrice": "46000.00",
  "changePercent": "2.22",
  "timeframe": "5m"
}
```

### WatchlistCreated
### WatchlistItemAdded
### AlertTriggered

---

## News Context

### NewsIngested
```json
{
  "eventId": "uuid",
  "eventType": "news.ingested",
  "occurredAt": "2025-12-16T10:30:00Z",
  "newsId": "uuid",
  "externalId": "external-123",
  "source": "coindesk",
  "title": "Bitcoin reaches new high",
  "publishedAt": "2025-12-16T10:00:00Z"
}
```

### NewsTagged
```json
{
  "eventId": "uuid",
  "eventType": "news.tagged",
  "occurredAt": "2025-12-16T10:30:00Z",
  "newsId": "uuid",
  "symbols": ["BTC", "BTCUSDT"],
  "categories": ["crypto", "markets"]
}
```

### HighImpactNewsDetected
```json
{
  "eventId": "uuid",
  "eventType": "news.high_impact_detected",
  "occurredAt": "2025-12-16T10:30:00Z",
  "newsId": "uuid",
  "symbols": ["BTC"],
  "impactScore": 0.85,
  "sentiment": "positive"
}
```

### SentimentChanged

---

## Exchange Context

### ExchangeConnected
```json
{
  "eventId": "uuid",
  "eventType": "exchange.connected",
  "occurredAt": "2025-12-16T10:30:00Z",
  "connectionId": "uuid",
  "userId": "uuid",
  "exchange": "binance",
  "testnet": false
}
```

### ExchangeDisconnected
### SymbolRulesUpdated
### RateLimitReached
```json
{
  "eventId": "uuid",
  "eventType": "exchange.rate_limit_reached",
  "occurredAt": "2025-12-16T10:30:00Z",
  "connectionId": "uuid",
  "exchange": "binance",
  "limitType": "REQUEST_WEIGHT",
  "limit": 1200,
  "used": 1200
}
```

### ExchangeHealthDegraded

---

## Portfolio Context

### AccountCreated
```json
{
  "eventId": "uuid",
  "eventType": "portfolio.account_created",
  "occurredAt": "2025-12-16T10:30:00Z",
  "accountId": "uuid",
  "userId": "uuid",
  "connectionId": "uuid",
  "name": "Binance Main"
}
```

### PositionOpened
```json
{
  "eventId": "uuid",
  "eventType": "portfolio.position_opened",
  "occurredAt": "2025-12-16T10:30:00Z",
  "accountId": "uuid",
  "positionId": "uuid",
  "symbol": "BTCUSDT",
  "asset": "BTC",
  "quantity": "0.1",
  "averageCost": "45000.00"
}
```

### PositionClosed
### BalanceUpdated
### ReconciliationCompleted
### DiscrepancyDetected
```json
{
  "eventId": "uuid",
  "eventType": "portfolio.discrepancy_detected",
  "occurredAt": "2025-12-16T10:30:00Z",
  "accountId": "uuid",
  "asset": "BTC",
  "expectedBalance": "1.5",
  "actualBalance": "1.49",
  "difference": "0.01"
}
```

---

## Trading Context

### OrderCreated
```json
{
  "eventId": "uuid",
  "eventType": "order.created",
  "occurredAt": "2025-12-16T10:30:00Z",
  "orderId": "uuid",
  "accountId": "uuid",
  "botId": "uuid",
  "proposalId": "uuid",
  "symbol": "BTCUSDT",
  "side": "buy",
  "type": "limit",
  "quantity": "0.1",
  "price": "45000.00"
}
```

### OrderSubmitted
```json
{
  "eventId": "uuid",
  "eventType": "order.submitted",
  "occurredAt": "2025-12-16T10:30:00Z",
  "orderId": "uuid",
  "exchangeOrderId": "12345",
  "submittedAt": "2025-12-16T10:30:00Z"
}
```

### OrderPartiallyFilled
### OrderFilled
```json
{
  "eventId": "uuid",
  "eventType": "order.filled",
  "occurredAt": "2025-12-16T10:30:00Z",
  "orderId": "uuid",
  "exchangeOrderId": "12345",
  "executedQuantity": "0.1",
  "executedPrice": "45000.00",
  "commission": "0.00001",
  "commissionAsset": "BTC",
  "totalCost": "4500.00"
}
```

### OrderCancelled
### OrderFailed

---

## Bots Context

### BotCreated
```json
{
  "eventId": "uuid",
  "eventType": "bot.created",
  "occurredAt": "2025-12-16T10:30:00Z",
  "botId": "uuid",
  "userId": "uuid",
  "accountId": "uuid",
  "name": "BTC Scalper",
  "mode": "auto_protected",
  "horizon": "short",
  "reserve": "5000.00"
}
```

### BotStarted
### BotPaused
### BotHalted

### DecisionProposed
```json
{
  "eventId": "uuid",
  "eventType": "bot.decision_proposed",
  "occurredAt": "2025-12-16T10:30:00Z",
  "botId": "uuid",
  "proposalId": "uuid",
  "symbol": "BTCUSDT",
  "side": "buy",
  "quantity": "0.1",
  "estimatedPrice": "45000.00",
  "justification": "Strong support level, RSI oversold",
  "risk": "medium",
  "requiresApproval": true
}
```

### DecisionApproved
```json
{
  "eventId": "uuid",
  "eventType": "bot.decision_approved",
  "occurredAt": "2025-12-16T10:30:00Z",
  "botId": "uuid",
  "proposalId": "uuid",
  "approvedBy": "user|system",
  "approvedAt": "2025-12-16T10:31:00Z"
}
```

### DecisionRejected
### DecisionExpired
### DecisionExecuted

---

## Risk Context

### RiskLimitDefined
```json
{
  "eventId": "uuid",
  "eventType": "risk.limit_defined",
  "occurredAt": "2025-12-16T10:30:00Z",
  "scope": "global",
  "limitType": "max_daily_loss",
  "value": "500.00",
  "userId": "uuid"
}
```

### RiskLimitBreached
```json
{
  "eventId": "uuid",
  "eventType": "risk.limit_breached",
  "occurredAt": "2025-12-16T10:30:00Z",
  "scope": "bot",
  "botId": "uuid",
  "limitType": "max_daily_loss",
  "limit": "100.00",
  "current": "105.00",
  "action": "halt"
}
```

### ExposureUpdated
### KillSwitchActivated
```json
{
  "eventId": "uuid",
  "eventType": "risk.kill_switch_activated",
  "occurredAt": "2025-12-16T10:30:00Z",
  "scope": "global",
  "reason": "Market anomaly detected",
  "activatedBy": "uuid"
}
```

### KillSwitchDeactivated
### NoTradeWindowOpened
### NoTradeWindowClosed

---

## Analytics Context

### ReportGenerated
### KpiUpdated
### BenchmarkCompared

---

## Audit Context

### AuditEventRecorded
```json
{
  "eventId": "uuid",
  "eventType": "audit.event_recorded",
  "occurredAt": "2025-12-16T10:30:00Z",
  "entityType": "order",
  "entityId": "uuid",
  "action": "created",
  "userId": "uuid",
  "context": {
    "ip": "192.168.1.1",
    "userAgent": "..."
  }
}
```

### SupportBundleGenerated

---

## Event Handlers

Les événements sont gérés de manière asynchrone via Symfony Messenger.

Exemple de handler :

```php
#[AsMessageHandler]
class WhenOrderFilledThenUpdatePortfolio
{
    public function __invoke(OrderFilled $event): void
    {
        // Update portfolio balances and positions
    }
}
```

---

## Event Store

Tous les événements sont stockés dans une table dédiée :

```sql
CREATE TABLE event_store (
    id UUID PRIMARY KEY,
    event_type VARCHAR(255) NOT NULL,
    aggregate_type VARCHAR(255) NOT NULL,
    aggregate_id UUID NOT NULL,
    occurred_at TIMESTAMP NOT NULL,
    payload JSONB NOT NULL,
    metadata JSONB,
    INDEX idx_aggregate (aggregate_type, aggregate_id),
    INDEX idx_event_type (event_type),
    INDEX idx_occurred_at (occurred_at)
);
```

---

## Projections

Les événements peuvent alimenter des projections read-model :

- Dashboard analytics
- Reports
- Audit trail
- Notifications
