# API Endpoints Specification

Documentation des endpoints API REST de la plateforme.

---

## Authentication

### POST /api/auth/register
Créer un compte utilisateur.

**Request**:
```json
{
  "email": "user@example.com",
  "password": "SecurePassword123!",
  "firstName": "John",
  "lastName": "Doe"
}
```

**Response** (201):
```json
{
  "userId": "uuid",
  "email": "user@example.com",
  "status": "pending_verification"
}
```

### POST /api/auth/login
Authentification.

**Request**:
```json
{
  "email": "user@example.com",
  "password": "SecurePassword123!"
}
```

**Response** (200):
```json
{
  "token": "jwt_token",
  "refreshToken": "refresh_token",
  "expiresIn": 3600,
  "user": {
    "id": "uuid",
    "email": "user@example.com",
    "firstName": "John",
    "mfaEnabled": true
  }
}
```

### POST /api/auth/mfa/enable
Activer MFA.

### POST /api/auth/mfa/verify
Vérifier code MFA.

---

## Markets

### GET /api/markets/assets
Liste des actifs.

**Query params**:
- `type`: crypto|stock|index|fx
- `search`: string
- `limit`: int
- `offset`: int

**Response**:
```json
{
  "items": [
    {
      "symbol": "BTCUSDT",
      "baseAsset": "BTC",
      "quoteAsset": "USDT",
      "name": "Bitcoin",
      "type": "crypto",
      "price": "45000.00",
      "change24h": "2.5",
      "volume24h": "1234567890"
    }
  ],
  "total": 150,
  "limit": 50,
  "offset": 0
}
```

### GET /api/markets/assets/{symbol}
Détail d'un actif.

**Response**:
```json
{
  "symbol": "BTCUSDT",
  "baseAsset": "BTC",
  "quoteAsset": "USDT",
  "name": "Bitcoin",
  "type": "crypto",
  "price": {
    "last": "45000.00",
    "bid": "44999.00",
    "ask": "45001.00",
    "timestamp": "2025-12-16T10:30:00Z"
  },
  "statistics": {
    "high24h": "46000.00",
    "low24h": "44000.00",
    "volume24h": "1234567890",
    "change24h": "2.5"
  }
}
```

### GET /api/markets/watchlists
Lister watchlists.

### POST /api/markets/watchlists
Créer watchlist.

### GET /api/markets/watchlists/{id}
Détail watchlist.

### PUT /api/markets/watchlists/{id}
Modifier watchlist.

### DELETE /api/markets/watchlists/{id}
Supprimer watchlist.

---

## Portfolio

### GET /api/portfolio/accounts
Liste des comptes.

**Response**:
```json
{
  "accounts": [
    {
      "id": "uuid",
      "name": "Binance Main",
      "exchange": "binance",
      "status": "connected",
      "totalValue": "10000.00",
      "totalValueUSD": "10000.00",
      "pnl24h": "250.00",
      "pnlPercent24h": "2.5"
    }
  ]
}
```

### GET /api/portfolio/accounts/{accountId}/positions
Positions d'un compte.

**Response**:
```json
{
  "positions": [
    {
      "symbol": "BTCUSDT",
      "asset": "BTC",
      "quantity": "0.5",
      "averageCost": "44000.00",
      "currentPrice": "45000.00",
      "marketValue": "22500.00",
      "unrealizedPnl": "500.00",
      "unrealizedPnlPercent": "2.27",
      "realizedPnl": "100.00"
    }
  ]
}
```

### GET /api/portfolio/accounts/{accountId}/ledger
Ledger (historique mouvements).

**Query params**:
- `startDate`: ISO date
- `endDate`: ISO date
- `asset`: string
- `type`: buy|sell|fee|transfer

**Response**:
```json
{
  "entries": [
    {
      "id": "uuid",
      "timestamp": "2025-12-16T10:30:00Z",
      "type": "buy",
      "symbol": "BTCUSDT",
      "asset": "BTC",
      "quantity": "0.1",
      "price": "45000.00",
      "total": "4500.00",
      "fee": "4.50",
      "feeAsset": "USDT",
      "orderId": "uuid"
    }
  ],
  "total": 250
}
```

---

## Bots

### GET /api/bots
Liste des bots.

**Response**:
```json
{
  "bots": [
    {
      "id": "uuid",
      "name": "BTC Scalper",
      "status": "running",
      "mode": "auto_protected",
      "horizon": "short",
      "reserve": "5000.00",
      "reserveUsed": "2000.00",
      "pnl": "150.00",
      "pnlPercent": "3.0",
      "winRate": "65.5",
      "tradesCount": 42,
      "createdAt": "2025-12-01T00:00:00Z"
    }
  ]
}
```

### POST /api/bots
Créer un bot.

**Request**:
```json
{
  "name": "BTC Scalper",
  "accountId": "uuid",
  "mode": "auto_protected",
  "horizon": "short",
  "reserve": "5000.00",
  "universe": ["BTCUSDT", "ETHUSDT"],
  "rules": {
    "maxPositionSize": "10.0",
    "maxPositions": 3,
    "maxLossPerDay": "100.00"
  }
}
```

### GET /api/bots/{id}
Détail d'un bot.

### PUT /api/bots/{id}
Modifier un bot.

### DELETE /api/bots/{id}
Supprimer un bot.

### POST /api/bots/{id}/start
Démarrer un bot.

### POST /api/bots/{id}/pause
Mettre en pause.

### POST /api/bots/{id}/halt
Arrêter définitivement.

### GET /api/bots/{id}/proposals
Propositions en attente.

**Response**:
```json
{
  "proposals": [
    {
      "id": "uuid",
      "status": "pending_approval",
      "symbol": "BTCUSDT",
      "side": "buy",
      "quantity": "0.1",
      "estimatedPrice": "45000.00",
      "estimatedValue": "4500.00",
      "justification": "Strong support level, RSI oversold",
      "risk": "medium",
      "createdAt": "2025-12-16T10:30:00Z",
      "expiresAt": "2025-12-16T11:00:00Z"
    }
  ]
}
```

### POST /api/bots/proposals/{proposalId}/approve
Approuver proposition.

### POST /api/bots/proposals/{proposalId}/reject
Rejeter proposition.

### GET /api/bots/{id}/decisions
Journal des décisions.

---

## Trading

### GET /api/trading/orders
Historique des ordres.

**Query params**:
- `accountId`: uuid
- `botId`: uuid
- `symbol`: string
- `status`: draft|sent|filled|cancelled|failed
- `startDate`: ISO date
- `endDate`: ISO date

### GET /api/trading/orders/{orderId}
Détail d'un ordre.

**Response**:
```json
{
  "id": "uuid",
  "exchangeOrderId": "12345",
  "symbol": "BTCUSDT",
  "side": "buy",
  "type": "limit",
  "quantity": "0.1",
  "price": "45000.00",
  "status": "filled",
  "executedQuantity": "0.1",
  "executedPrice": "45000.00",
  "fills": [
    {
      "tradeId": "67890",
      "price": "45000.00",
      "quantity": "0.1",
      "commission": "0.00001",
      "commissionAsset": "BTC",
      "timestamp": "2025-12-16T10:30:00Z"
    }
  ],
  "createdAt": "2025-12-16T10:29:00Z",
  "updatedAt": "2025-12-16T10:30:00Z"
}
```

---

## News

### GET /api/news
Flux de news.

**Query params**:
- `symbols`: string[] (comma-separated)
- `impact`: low|medium|high
- `sentiment`: positive|negative|neutral
- `limit`: int
- `offset`: int

**Response**:
```json
{
  "items": [
    {
      "id": "uuid",
      "title": "Bitcoin reaches new high",
      "snippet": "Bitcoin price surges past $45,000...",
      "source": "CoinDesk",
      "url": "https://...",
      "publishedAt": "2025-12-16T10:00:00Z",
      "symbols": ["BTC", "BTCUSDT"],
      "impact": "high",
      "sentiment": "positive",
      "impactScore": 0.85
    }
  ],
  "total": 500
}
```

### GET /api/news/{id}
Détail d'une news.

---

## Risk

### GET /api/risk/limits
Limites de risque.

**Response**:
```json
{
  "global": {
    "maxDailyLoss": "500.00",
    "currentDailyLoss": "50.00",
    "maxExposure": "10000.00",
    "currentExposure": "5000.00"
  },
  "bots": [
    {
      "botId": "uuid",
      "maxDailyLoss": "100.00",
      "currentDailyLoss": "10.00"
    }
  ]
}
```

### PUT /api/risk/limits
Modifier limites.

### GET /api/risk/exposure
Exposition actuelle.

### POST /api/risk/kill-switch
Activer kill switch.

**Request**:
```json
{
  "scope": "global",
  "reason": "Market anomaly detected"
}
```

### DELETE /api/risk/kill-switch
Désactiver kill switch.

---

## Settings

### GET /api/settings/connections
Connexions exchanges.

### POST /api/settings/connections
Ajouter connexion.

**Request**:
```json
{
  "exchange": "binance",
  "name": "My Binance Account",
  "apiKey": "key",
  "apiSecret": "secret",
  "testnet": false
}
```

### DELETE /api/settings/connections/{id}
Supprimer connexion.

### GET /api/settings/notifications
Paramètres notifications.

### PUT /api/settings/notifications
Modifier notifications.

---

## Codes d'erreur

- `400` : Bad Request
- `401` : Unauthorized
- `403` : Forbidden
- `404` : Not Found
- `422` : Validation Error
- `429` : Rate Limit Exceeded
- `500` : Internal Server Error
- `503` : Service Unavailable

**Format erreur**:
```json
{
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Invalid request data",
    "details": [
      {
        "field": "email",
        "message": "Invalid email format"
      }
    ]
  }
}
```
