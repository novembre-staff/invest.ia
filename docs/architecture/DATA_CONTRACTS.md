# Data Contracts - Interfaces d'intégration

Ce document définit les contrats fonctionnels pour les intégrations externes (exchanges, news providers, market data).

---

## ExchangeAdapter (Interface)

Interface standardisée pour tous les exchanges. Permet d'ajouter de nouveaux exchanges sans modifier le domain.

### Responsabilités

- Connexion et authentification
- Récupération des règles de symboles
- Gestion des balances
- Soumission et suivi d'ordres
- Récupération des fills/trades
- Health check et rate limiting

### Contrat fonctionnel

```php
interface ExchangeAdapterInterface
{
    // Connection
    public function connect(ExchangeCredentials $credentials): ConnectionResult;
    public function disconnect(): void;
    public function ping(): HealthStatus;
    
    // Symbol Rules
    public function getSymbolRules(string $symbol): SymbolRules;
    public function getAllSymbolRules(): SymbolRulesCollection;
    
    // Balances
    public function getBalances(): BalanceCollection;
    public function getBalance(string $asset): Balance;
    
    // Orders
    public function createOrder(OrderRequest $order): OrderResult;
    public function cancelOrder(string $orderId): CancelResult;
    public function getOrder(string $orderId): OrderStatus;
    public function getOpenOrders(?string $symbol = null): OrderCollection;
    
    // Fills/Trades
    public function getTrades(string $orderId): TradeCollection;
    public function getAccountTrades(
        ?string $symbol = null,
        ?DateTime $startTime = null,
        ?DateTime $endTime = null
    ): TradeCollection;
    
    // Rate Limiting
    public function getRateLimitStatus(): RateLimitStatus;
    
    // Health
    public function getHealthStatus(): HealthStatus;
}
```

### Data Structures

#### SymbolRules

```php
class SymbolRules
{
    public string $symbol;
    public string $baseAsset;
    public string $quoteAsset;
    public string $status;  // TRADING, BREAK, etc.
    
    // Quantity rules
    public BigDecimal $minQuantity;
    public BigDecimal $maxQuantity;
    public BigDecimal $stepSize;
    public int $quantityPrecision;
    
    // Price rules
    public BigDecimal $tickSize;
    public int $pricePrecision;
    
    // Notional rules
    public BigDecimal $minNotional;
    public ?BigDecimal $maxNotional;
    
    // Other
    public array $orderTypes;  // LIMIT, MARKET, etc.
    public array $timeInForce; // GTC, IOC, FOK
}
```

#### Balance

```php
class Balance
{
    public string $asset;
    public BigDecimal $free;
    public BigDecimal $locked;
    public BigDecimal $total;
    public DateTime $updatedAt;
}
```

#### OrderResult

```php
class OrderResult
{
    public string $orderId;           // Exchange order ID
    public string $clientOrderId;     // Our internal ID
    public string $symbol;
    public OrderStatus $status;
    public BigDecimal $executedQuantity;
    public BigDecimal $cummulativeQuoteQty;
    public DateTime $transactTime;
    public ?string $errorCode;
    public ?string $errorMessage;
}
```

#### Trade

```php
class Trade
{
    public string $tradeId;           // Exchange trade ID
    public string $orderId;
    public string $symbol;
    public BigDecimal $price;
    public BigDecimal $quantity;
    public BigDecimal $quoteQuantity;
    public BigDecimal $commission;
    public string $commissionAsset;
    public DateTime $time;
    public bool $isBuyer;
    public bool $isMaker;
}
```

#### HealthStatus

```php
class HealthStatus
{
    public bool $isHealthy;
    public int $latencyMs;
    public ?string $errorMessage;
    public DateTime $checkedAt;
}
```

#### RateLimitStatus

```php
class RateLimitStatus
{
    public string $rateLimitType;     // REQUEST_WEIGHT, ORDERS, etc.
    public int $interval;
    public string $intervalUnit;      // SECOND, MINUTE, DAY
    public int $limit;
    public int $used;
    public int $remaining;
    public ?DateTime $resetAt;
}
```

---

## NewsProvider (Interface)

Interface standardisée pour les fournisseurs d'actualités.

### Contrat fonctionnel

```php
interface NewsProviderInterface
{
    public function fetchNews(
        ?DateTime $since = null,
        ?array $symbols = null,
        ?int $limit = null
    ): NewsItemCollection;
    
    public function getNewsById(string $externalId): NewsItem;
    
    public function getProviderInfo(): ProviderInfo;
}
```

### Data Structures

#### NewsItem

```php
class NewsItem
{
    public string $externalId;        // ID du provider
    public string $source;            // Nom du provider
    public string $title;
    public ?string $content;
    public ?string $snippet;
    public DateTime $publishedAt;
    public DateTime $fetchedAt;
    public string $language;
    public string $url;
    public array $symbols;            // Tickers/coins détectés
    public ?float $impactScore;       // 0-1
    public ?string $sentiment;        // positive, negative, neutral
    public ?int $reliabilityScore;    // 0-100
    public array $categories;
    public array $tags;
}
```

#### ProviderInfo

```php
class ProviderInfo
{
    public string $name;
    public string $type;              // RSS, API, Scraper
    public bool $supportsSymbols;
    public bool $supportsImpact;
    public bool $supportsSentiment;
    public ?int $rateLimitPerHour;
}
```

---

## MarketDataProvider (Interface)

Interface standardisée pour les fournisseurs de données de marché.

### Contrat fonctionnel

```php
interface MarketDataProviderInterface
{
    // Tick data
    public function getTicker(string $symbol): Ticker;
    public function getTickers(?array $symbols = null): TickerCollection;
    
    // OHLCV
    public function getOHLCV(
        string $symbol,
        string $interval,
        ?DateTime $startTime = null,
        ?DateTime $endTime = null,
        ?int $limit = null
    ): OHLCVCollection;
    
    // Order book
    public function getOrderBook(string $symbol, int $depth = 20): OrderBook;
    
    // Trades
    public function getRecentTrades(string $symbol, int $limit = 100): TradeCollection;
    
    // Quality
    public function getDataQuality(string $symbol): DataQuality;
}
```

### Data Structures

#### Ticker

```php
class Ticker
{
    public string $symbol;
    public BigDecimal $last;
    public BigDecimal $bid;
    public BigDecimal $ask;
    public BigDecimal $high24h;
    public BigDecimal $low24h;
    public BigDecimal $volume24h;
    public BigDecimal $quoteVolume24h;
    public BigDecimal $priceChange24h;
    public float $priceChangePercent24h;
    public DateTime $timestamp;
}
```

#### OHLCV

```php
class OHLCV
{
    public DateTime $openTime;
    public BigDecimal $open;
    public BigDecimal $high;
    public BigDecimal $low;
    public BigDecimal $close;
    public BigDecimal $volume;
    public DateTime $closeTime;
}
```

#### OrderBook

```php
class OrderBook
{
    public string $symbol;
    public array $bids;               // [[price, quantity], ...]
    public array $asks;               // [[price, quantity], ...]
    public DateTime $timestamp;
}
```

#### DataQuality

```php
class DataQuality
{
    public string $symbol;
    public bool $isStale;             // Prix trop vieux
    public int $staleDurationSeconds;
    public bool $hasMissingData;
    public float $completenessScore;  // 0-1
    public DateTime $lastUpdate;
}
```

---

## Implémentations

### Pour Binance

```
Exchange/Infrastructure/Adapter/BinanceAdapter.php
```

### Pour ajouter un nouvel exchange

1. Créer `Exchange/Infrastructure/Adapter/NewExchangeAdapter.php`
2. Implémenter `ExchangeAdapterInterface`
3. Mapper les réponses API vers nos structures
4. Gérer les spécificités (rate limits, authentification)
5. Ajouter tests d'intégration

### Pour ajouter un nouveau news provider

1. Créer `News/Infrastructure/Adapter/NewProviderAdapter.php`
2. Implémenter `NewsProviderInterface`
3. Parser/normaliser les données
4. Ajouter extraction de symboles si non fournie

---

## Principes

- **Isolation** : le domain ne doit jamais dépendre d'un exchange spécifique
- **Mapping** : toujours mapper les structures externes vers nos structures internes
- **Validation** : valider les données en entrée
- **Erreurs** : lever des exceptions typées en cas d'erreur
- **Logs** : logger les appels API (sans secrets)
- **Retry** : gérer les erreurs transientes avec retry
- **Circuit breaker** : couper si trop d'erreurs

---

## Tests

Chaque adapter doit avoir :
- Tests unitaires (avec mocks)
- Tests d'intégration (sandbox exchange)
- Tests de contrat (respect de l'interface)
- Tests d'erreur (rate limits, network, etc.)
