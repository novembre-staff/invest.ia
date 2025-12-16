# Bounded Contexts - Architecture DDD

Ce document détaille chaque bounded context de la plateforme invest.ia.

---

## Shared

**Responsabilité** : Éléments partagés et réutilisables dans toute l'application.

**Contenu** :
- Domain : Interfaces communes, types de base, value objects génériques
- Application : Services transverses, helpers
- Infrastructure : Configuration, utilitaires techniques
- UI : Middlewares, filters, exception handlers globaux

---

## Identity

**Responsabilité** : Gestion des utilisateurs, authentification, autorisation.

**Agrégats** :
- User
- Session
- Role
- Permission

**Événements clés** :
- UserRegistered
- UserEmailVerified
- MfaEnabled
- MfaDisabled
- UserLoggedIn
- UserLoggedOut
- PasswordChanged
- SessionExpired

**Règles métier** :
- MFA fortement recommandé pour accès trading
- Sessions expirées après inactivité
- Rotation des tokens obligatoire
- Historique des connexions pour audit

---

## Market

**Responsabilité** : Données de marché, assets, prix, indicateurs.

**Agrégats** :
- Asset (crypto, actions, indices, FX, commodities)
- Price (tick, OHLCV)
- Watchlist
- MarketIndicator

**Événements clés** :
- AssetRegistered
- PriceUpdated
- SignificantPriceMove
- WatchlistCreated
- WatchlistItemAdded
- AlertTriggered

**Règles métier** :
- Prix horodatés avec indicateur de fraîcheur
- Consolidation multi-sources avec quality flags
- Alertes configurables sur variations
- Screener avec filtres multiples

---

## News

**Responsabilité** : Ingestion, normalisation, tagging et scoring des actualités.

**Agrégats** :
- NewsItem
- NewsSource
- NewsImpact
- NewsTag

**Événements clés** :
- NewsIngested
- NewsTagged
- HighImpactNewsDetected
- SentimentChanged

**Règles métier** :
- Déduplication par contenu similaire
- Extraction automatique des entités (tickers, coins)
- Scoring impact et sentiment
- Timeline unifiée news + prix + actions bots

---

## Exchange

**Responsabilité** : Connexion aux exchanges, santé, rules, rate limits.

**Agrégats** :
- ExchangeConnection
- SymbolRules
- ExchangeHealth
- RateLimitTracker

**Événements clés** :
- ExchangeConnected
- ExchangeDisconnected
- SymbolRulesUpdated
- RateLimitReached
- ExchangeHealthDegraded

**Règles métier** :
- API keys chiffrées, permissions minimales
- Vérification scopes à la connexion
- Circuit breaker sur erreurs répétées
- Retry avec backoff exponentiel
- Monitoring latence et disponibilité

---

## Portfolio

**Responsabilité** : Comptes, positions, ledger, réconciliation.

**Agrégats** :
- PortfolioAccount
- Position
- LedgerEntry
- Balance
- Reconciliation

**Événements clés** :
- AccountCreated
- PositionOpened
- PositionClosed
- BalanceUpdated
- ReconciliationCompleted
- DiscrepancyDetected

**Règles métier** :
- Multi-comptes par utilisateur
- Ledger double-entry pour traçabilité
- Réconciliation périodique avec exchange
- Calcul P&L réalisé (FIFO) et non réalisé
- Support multi-devises avec conversions

---

## Trading

**Responsabilité** : Ordres, exécution, fills, state machine.

**Agrégats** :
- Order
- OrderFill
- IdempotencyKey

**Événements clés** :
- OrderCreated
- OrderSubmitted
- OrderPartiallyFilled
- OrderFilled
- OrderCancelled
- OrderFailed

**Règles métier** :
- State machine stricte (Draft → Sent → Filled/Cancelled/Failed)
- Idempotence obligatoire
- Normalisation quantités/prix selon symbol rules
- Journalisation complète
- Déduplication des fills

---

## Bots

**Responsabilité** : Agents décisionnels, réserves, règles, propositions.

**Agrégats** :
- Bot
- BotReserve
- BotRuleSet
- BotDecision
- TradePlan

**Événements clés** :
- BotCreated
- BotStarted
- BotPaused
- BotHalted
- DecisionProposed
- DecisionApproved
- DecisionRejected
- DecisionExpired
- DecisionExecuted

**Règles métier** :
- 3 modes : Conseil, Auto protégé, Auto
- Réserve isolée par bot
- Proposition obligatoire avant exécution
- Validation selon mode et confiance
- Horizon : court/moyen/long
- Justification systématique
- Invalidation automatique si conditions changent

---

## Risk

**Responsabilité** : Limites, exposition, kill switch, no-trade windows.

**Agrégats** :
- RiskLimit
- Exposure
- KillSwitch
- NoTradeWindow

**Événements clés** :
- RiskLimitDefined
- RiskLimitBreached
- ExposureUpdated
- KillSwitchActivated
- KillSwitchDeactivated
- NoTradeWindowOpened
- NoTradeWindowClosed

**Règles métier** :
- Limites globales + par bot
- Kill switch global et par bot
- Vérification pré-exécution obligatoire
- Arrêt immédiat si perte max atteinte
- No-trade sur volatilité extrême
- Monitoring exposition par actif/secteur

---

## Analytics

**Responsabilité** : KPIs, performance, reporting, métriques.

**Agrégats** :
- PerformanceReport
- KPI
- Benchmark

**Événements clés** :
- ReportGenerated
- KpiUpdated
- BenchmarkCompared

**Règles métier** :
- Calcul P&L par bot, par actif, global
- Métriques : win-rate, sharpe, drawdown
- Comparaison vs benchmarks
- Export CSV/PDF
- Historisation

---

## Audit

**Responsabilité** : Audit trail, exports, support bundles.

**Agrégats** :
- AuditEvent
- AuditLog
- SupportBundle

**Événements clés** :
- AuditEventRecorded
- SupportBundleGenerated

**Règles métier** :
- Tout événement métier auditable
- Traçabilité : qui, quoi, quand, pourquoi, résultat
- Immutabilité des logs
- Rétention configurable
- Support bundle : snapshot complet pour investigation

---

## Relations entre Bounded Contexts

```
Identity → Exchange (user owns connections)
Exchange → Market (fetch prices, symbols)
Exchange → Trading (submit orders)
Exchange → Portfolio (balances, positions)
Portfolio ← Trading (fills update positions)
Bots → Market (read prices, indicators)
Bots → News (consume impacts)
Bots → Risk (check limits)
Bots → Trading (create orders)
Risk → Portfolio (check exposure)
Risk → Trading (pre-execution checks)
Analytics → Portfolio (read P&L)
Analytics → Bots (performance metrics)
Audit ← * (all contexts emit events)
```

---

## Principes de communication inter-contextes

- **Événements** : communication asynchrone via Symfony Messenger
- **Queries** : lectures via interfaces/services
- **Commands** : actions via command bus
- **Anti-corruption layer** : adaptateurs pour isolation

