# Dossier de conception

## Dashboard Finance + Crypto, News, Bots conseillers/exécutants (argent réel)

> Objectif : fournir un dossier **ultra actionnable** (sans code) pour que les devs puissent construire une V1 solide. **Pas de backtest / pas de paper trading**.

---

## 0) Arborescence parfaite du projet (Symfony + DDD/Hexa)

### 0.1 Vue d'ensemble repo

```
project-root/
├─ apps/
│  ├─ web/                          # Front (SPA ou Symfony UX)
│  │  ├─ src/
│  │  ├─ package.json
│  │  └─ ...
│  └─ api/                          # Symfony (Back)
│     ├─ bin/
│     ├─ config/
│     ├─ public/
│     ├─ src/
│     ├─ tests/
│     └─ ...
├─ docs/
│  ├─ conception/                   # Dossier de conception + specs
│  ├─ architecture/                 # diagrammes, standards
│  ├─ adr/                          # Architecture Decision Records
│  └─ runbooks/                     # incidents / opérations
├─ infra/
│  ├─ docker/
│  ├─ k8s/                          # optionnel
│  └─ terraform/                    # optionnel
└─ .github/
   ├─ workflows/
   └─ agents/
```

### 0.2 Symfony (apps/api/src) — Bounded Contexts

```
apps/api/src/
├─ Shared/
│  ├─ Domain/
│  ├─ Application/
│  ├─ Infrastructure/
│  └─ UI/
│
├─ Identity/                        # Auth, MFA, sessions, rôles
├─ Market/                          # Assets, prices, FX, OHLCV, data quality
├─ News/                            # Ingestion news, tagging, scoring
├─ Exchange/                        # Binance, symbol rules, health, rate limits
├─ Portfolio/                       # Comptes, positions, ledger, reconciliation
├─ Trading/                         # Orders, fills, state machine, idempotence
├─ Bots/                            # Bots, reserve, rules, decisions, approvals
├─ Risk/                            # Risk Center, limites, kill switch, no-trade
├─ Analytics/                       # KPIs, performance, reporting
└─ Audit/                           # Audit trail, support bundle, exports
```

### 0.3 Pattern interne de chaque contexte

```
<Context>/
├─ Domain/
│  ├─ Model/
│  ├─ ValueObject/
│  ├─ Repository/                   # interfaces
│  ├─ Service/
│  └─ Event/
├─ Application/
│  ├─ Command/
│  ├─ Query/
│  ├─ Handler/
│  ├─ DTO/
│  └─ Service/
├─ Infrastructure/
│  ├─ Persistence/Doctrine/
│  ├─ Adapter/
│  ├─ Messaging/
│  └─ Mapper/
└─ UI/
   ├─ Http/Controller/
   ├─ Http/Request/
   ├─ Http/Response/
   └─ Console/Command/
```

### 0.4 Jobs & async (Symfony Messenger)

* Ingestion news
* Refresh market data
* Bot tick/orchestration
* Order status sync
* Portfolio reconciliation

---

## 1) Vision produit

### 1.1 Proposition de valeur

Une plateforme web (front + back) qui agrège :

* **Marchés finance "classiques" + crypto** (prix, variations, indicateurs, watchlists)
* **News finance + crypto** (multi-sources, tags actifs, scoring d'impact)
* **Bots** qui **proposent** puis **exécutent** des investissements/trades avec **argent réel** via **Binance** (et extensible à d'autres brokers/exchanges), avec :

  * une **réserve/budget alloué** par bot
  * une **stratégie** et un **horizon** (court/moyen/long)
  * une **justification** de chaque décision
  * un **workflow de validation utilisateur** (obligatoire selon le mode)
  * un suivi complet : performance, risques, journal d'actions, audit

### 1.2 Personae

* **Débutant** : veut un dashboard clair, des alertes, et des propositions "assistées" avec validation.
* **Intermédiaire** : plusieurs bots, règles de risque, auto protégé, reporting.
* **Avancé** : suivi fin, journaux détaillés, contrôle des limites, multi-portefeuilles.

### 1.3 Non-objectifs explicites (V1)

* Pas de backtesting
* Pas de paper trading
* Pas de futures/levier au départ (optionnel plus tard)

---

## 2) Parcours utilisateur (UX flows)

### 2.1 Onboarding

1. Création compte + vérification email
2. Activation MFA (fortement recommandé)
3. Création d'une **watchlist** (crypto + actions/indices)
4. Connexion Binance (API Key/Secret) avec **permissions minimales** + (option) restriction IP
5. Création du 1er bot :

   * choisir univers (ex: Top 20 crypto)
   * horizon (court/moyen/long)
   * mode (Conseil / Auto protégé / Auto)
   * budget alloué (réserve)
   * règles de risque (perte max/jour, taille max position, actifs autorisés)

### 2.2 Cycle "proposition → validation → exécution"

1. Bot détecte une opportunité (signaux + contexte)
2. Bot génère une **Proposition** (TradePlan) :

   * actif, sens (buy/sell), quantité/valeur
   * horizon
   * justification + conditions d'invalidation
   * risque estimé (niveau)
   * ordre(s) proposés (type, limites, stop/tp si supporté)
3. Selon mode :

   * **Conseil** : validation utilisateur obligatoire
   * **Auto protégé** : auto si règles OK + confiance >= seuil, sinon validation
   * **Auto** : exécution automatique (avec kill switch)
4. Exécution Binance : création ordre, suivi fills, mise à jour portefeuille
5. Journalisation complète : décision + exécution + résultat

### 2.3 Gestion des incidents

* Si connecteur Binance KO : bots se mettent en **PAUSE**
* Si perte max atteinte : bot **HALT** jusqu'à décision utilisateur
* Si volatilité extrême : **no-trade window** configurable

---

## 3) Écrans & IA (spécifications UI)

### 3.1 Navigation

* **Overview** (dashboard global)
* **Markets** (screener + fiches actifs)
* **Portfolio** (positions, P&L, ledger, mouvements)
* **Bots** (liste + détail + inbox validations)
* **News** (flux global + filtre par actifs/watchlists)
* **Risk Center** (limites, exposition, kill switch, règles)
* **Settings** (connexions, sécurité, notifications)

### 3.2 Overview (dashboard global)

Composants :

* Bandeau "Market Pulse" : indices, FX, taux, commodities + BTC/ETH
* Valeur portefeuille + P&L (jour/semaine/mois) + drawdown
* Allocation par classe (Crypto / Actions / Cash …)
* Watchlists (top movers, alerts)
* Cartes Bots :

  * statut (RUN/PAUSE/HALT)
  * P&L
  * dernier trade
  * prochaine action attendue
  * compteur propositions en attente
* Module News : breaking + "impact élevé sur watchlist"

### 3.3 Markets

* Screener multi-actifs (crypto + finance)
* Fiche actif :

  * prix, variations, volume
  * mini-graphe
  * métriques clés (volatilité, high/low)
  * timeline unifiée : **prix + news + actions bots**

### 3.4 Portfolio

* Positions (réel via Binance + autres comptes plus tard)
* P&L réalisé / non réalisé
* Ledger (mouvements, fees, conversions)
* Historique ordres + statut
* Export CSV/PDF

### 3.5 News

* Flux multi-sources
* Filtres : actif, catégorie, impact, sentiment
* "News intelligence" : tag actif(s), score impact, résumé
* Alertes configurables

### 3.6 Bots

* Liste : statut, stratégie, horizon, réserve, P&L, drawdown, win-rate (si applicable)
* Détail bot :

  * performance
  * journal (décisions, validations, ordres)
  * règles risque
  * réserve & utilisation
* Inbox validations : propositions à approuver/rejeter/modifier

### 3.7 Risk Center

* Limites globales + par bot
* Exposition par actif/secteur/devise
* Alertes de risque (perte max, corrélation forte, concentration)
* Kill switch : global + par bot

### 3.8 Settings

* Connexion Binance (scopes, statut, rotation)
* Notifications (email/push/telegram)
* Sécurité (MFA, sessions)

---

## 4) Conception des Bots

### 4.1 Concepts

* Un bot = **agent décisionnel** sur un univers d'actifs.
* Chaque bot possède une **réserve** (budget alloué), isolée.
* Chaque bot produit des **Propositions** avant ordres.

### 4.2 Modes

* **Conseil** : tout passe par validation.
* **Auto protégé** : auto si garde-fous OK.
* **Auto** : tout auto (réservé, fortement encadré).

### 4.3 Horizons

* Court : minutes/heures (low time-in-market)
* Moyen : jours/semaines
* Long : semaines/mois (accumulation, DCA-like possible)

### 4.4 Réserve & sizing

* Réserve en devise de base (ex: USDT)
* Règles :

  * taille max position (ex: 10% réserve)
  * nb max positions simultanées
  * perte max par jour / semaine
  * exposition max par actif

### 4.5 Décision (structure attendue)

* Signal(s) déclencheurs
* Contexte (volatilité, tendance, news)
* Hypothèse
* Plan : entrée, taille, invalidation
* Risque : niveau + justification
* Demande de validation (si requis)

---

## 5) News & Intelligence

### 5.1 Pipeline news

* Ingestion multi-sources
* Normalisation : titre, source, date, langue
* Extraction entités : tickers/coins
* Résumé + scoring (impact/sentiment)
* Stockage + indexation

### 5.2 Ce que consomment les bots

* "Breaking sur actif surveillé"
* "Impact élevé"
* "Changement de sentiment"

---

## 6) Argent réel, exécution Binance

### 6.1 Principes

* L'exchange est **source de vérité pour les ordres**, mais l'app maintient un **ledger interne**.
* Tout ordre a un cycle de vie interne : Draft → PendingApproval → Sent → (PartiallyFilled) → Filled/Cancelled/Failed.

### 6.2 Sécurité API keys

* Chiffrement côté serveur
* Permissions minimales
* Rotation + révocation
* Option : restriction IP

### 6.3 Robustesse

* Gestion rate limit / retries
* Idempotency (clé interne) pour éviter doublons
* Circuit breaker si erreurs répétées

---

## 7) Modèle de données (DDD — agrégats)

### 7.1 Bounded Contexts

* Identity & Security
* Exchange Connectivity
* Portfolio & Ledger
* Orders & Execution
* Bots & Decisions
* News
* Analytics & Reporting
* Audit & Compliance

### 7.2 Agrégats principaux (exemples)

* User
* ExchangeConnection
* Asset
* Watchlist
* Portfolio
* LedgerEntry
* Bot
* BotReserve
* BotRuleSet
* BotDecision
* TradePlan
* Order
* OrderFill
* NewsItem
* NewsImpact
* NotificationRule
* AuditEvent

### 7.3 Événements clés (event model)

* ExchangeConnected
* BotCreated / BotPaused / BotHalted
* DecisionProposed
* DecisionApproved / DecisionRejected / DecisionModified
* OrderSubmitted
* OrderFilled / PartiallyFilled / Cancelled / Failed
* RiskLimitTriggered
* NewsBreaking

---

## 8) APIs (contrats)

### 8.1 API publique (front)

* Auth : session/JWT
* Endpoints :

  * Markets (prices, assets)
  * Portfolio (positions, ledger)
  * Bots (list, detail, proposals, actions)
  * News (feed, filters)
  * Risk (limits, kill switch)
  * Settings (connections, notifications)

### 8.2 Webhooks / jobs

* Binance user data stream (si utilisé)
* Jobs de synchronisation :

  * prices refresh
  * order status refresh
  * portfolio reconciliation
  * news ingestion
  * bot tick/orchestration

---

## 9) Observabilité & audit

### 9.1 Audit trail

* Chaque décision et chaque ordre doivent être auditables :

  * qui (user/bot)
  * quoi
  * quand
  * pourquoi (résumé)
  * résultat

### 9.2 Journal bot

* Timeline : signaux → décision → validation → ordre → fill → impact P&L

### 9.3 Monitoring

* Santé connecteurs (Binance)
* Latence jobs
* Taux d'erreurs

---

## 9bis) Spécification précision & calculs (ultra important)

> Objectif : éviter les bugs de chiffres, les incohérences et les litiges utilisateurs. Cette section définit les règles de précision, d'arrondi et de calcul P&L/fees.

### 9bis.1 Règles de précision, quantités et arrondis

**Source de vérité** : pour chaque symbole, récupérer et stocker les **symbol rules** de l'exchange (ex: minQty, stepSize, minNotional, tickSize, precision). Ces règles ne doivent jamais être "codées en dur".

**Règles fonctionnelles** :

* Toutes les valeurs monétaires et quantités sont stockées en **décimal** (pas float).
* Toute quantité/price envoyée à l'exchange est **normalisée** selon :

  * **step size** (quantité) → arrondi *down* par défaut (ne jamais dépasser le budget)
  * **tick size** (prix) → arrondi selon type d'ordre :

    * BUY limit : arrondi prix *down* (plus agressif = risque d'exécution, mais évite surpayer)
    * SELL limit : arrondi prix *up* (évite vendre trop bas)
  * **minQty / minNotional** : si non respecté → la proposition doit être marquée "invalid" avant validation utilisateur.

**Champs de référence à maintenir** (par Asset/Symbol) :

* baseAsset, quoteAsset
* quantityStep, priceTick
* minQuantity, minNotional
* quantityPrecision, pricePrecision

**Affichage UI** :

* Afficher à l'utilisateur des nombres "propres" (formatés) mais conserver l'exactitude en stockage.

### 9bis.2 Modèle de prix de référence (éviter les "P&L fantômes")

Définir un **Price Reference Policy** par type d'affichage/calcul :

* **Portfolio valuation** (valeur actuelle) :

  * Crypto spot : utiliser *mid price* si disponible (bestBid+bestAsk)/2, sinon last.
* **Unrealised P&L** : même politique que la valuation (mid préféré), afin de réduire l'effet spread.
* **Realised P&L** : basé uniquement sur les **fills** exécutés (prix réel).

Mécanisme anti-latence :

* Horodater chaque prix utilisé et afficher un indicateur "stale" si > X secondes.

### 9bis.3 Ledger, fees, conversions, P&L

**Ledger interne** : chaque fill produit des écritures comptables minimales :

* mouvements de baseAsset et quoteAsset
* fees (asset fee + montant)
* timestamp exchange
* IDs (orderId, tradeId)

**P&L realised** :

* Reposera sur la méthode de coût choisie (définir dès V1) :

  * recommandé : **FIFO** pour simplicité et transparence.
* Chaque vente déclenche un matching des quantités vendues avec les lots achetés (FIFO) → calcul gain/perte en quote (ex: USDT).

**P&L unrealised** :

* (prix de référence - coût moyen restant) × quantité restante
* coût restant = somme des lots ouverts (FIFO) ou coût moyen (si vous choisissez AVG). Choisir 1 méthode unique pour V1.

**Fees** :

* Les fees doivent être imputés au coût des lots (pour BUY) ou déduits du produit (pour SELL), selon la nature et l'asset de fee.
* En cas de fees payés dans un asset tiers (ex: BNB), stocker et valoriser ces fees avec une conversion au moment du fill (prix reference du moment).

**Conversions d'affichage (ex: USDT→EUR)** :

* Définir une **Reporting Currency** par utilisateur (ex: EUR).
* Conversion = valeur en quote (ex: USDT) × FX(USDT/EUR) à l'instant T (ou dernier FX connu).
* Stocker la source FX et l'horodatage.

---

## 9ter) State machines (propositions & ordres)

> Objectif : supprimer les incohérences. Les transitions doivent être explicites et auditables.

### 9ter.1 State machine — Proposition Bot (TradePlan)

États :

* **Draft** : en cours de construction (interne bot)
* **Proposed** : proposition finalisée, prête à soumission
* **PendingApproval** : visible utilisateur, en attente de décision
* **Approved** : validée (éventuellement avec modifications)
* **Rejected** : refusée
* **Expired** : expirée (conditions marché non valides / timeout)
* **Executed** : ordre(s) transmis et exécution lancée
* **Failed** : échec (validation, règles, exécution)

Transitions typiques :

* Draft → Proposed (bot finalise)
* Proposed → PendingApproval (soumission)
* PendingApproval → Approved / Rejected / Expired
* Approved → Executed (envoi ordre)
* Approved → Failed (contrôles finaux échouent)
* Executed → Failed (ordre non soumis / erreur exchange)

Règle : une proposition ne peut pas être exécutée si elle n'est pas Approved (sauf mode Auto où l'approval est implicite mais toujours journalisé).

### 9ter.2 State machine — Ordre (Order)

États :

* **Draft** : ordre préparé
* **Sent** : soumis à l'exchange
* **PartFilled** : partiellement exécuté
* **Filled** : exécuté entièrement
* **Cancelled** : annulé
* **Failed** : rejet/erreur

Transitions :

* Draft → Sent
* Sent → PartFilled → Filled
* Sent → Cancelled
* Sent → Failed
* PartFilled → Cancelled (si annulation possible)

Chaque transition doit générer un **AuditEvent**.

---

## 9quater) Idempotence & anti-doublons (prod)

> Objectif : éviter doublons d'ordres, doubles comptabilisations, replays webhooks.

### 9quater.1 Clé d'idempotence

* Chaque action "submit order" doit porter une **idempotencyKey** générée côté plateforme (ex: hash(UserId+BotId+TradePlanId+timestamp bucket)).
* Stocker idempotencyKey sur l'ordre et refuser toute nouvelle soumission identique.

### 9quater.2 Déduplication events exchange

* Stocker les identifiants uniques : orderId, tradeId (fill), eventTime.
* Si un fill (tradeId) est reçu 2 fois → ignorer la répétition.

### 9quater.3 Exactly-once logique

* Les écritures ledger sont créées **uniquement** à partir des fills **dédupliqués**.
* Toute réconciliation doit être capable de recalculer sans dupliquer (upsert par tradeId).

---

## 9quinquies) Data contracts — intégrations (extensibilité)

> Objectif : rendre la plateforme extensible sans refonte (Binance → autre exchange, multi news providers, multi data providers).

### 9quinquies.1 ExchangeAdapter (Spot) — contrat fonctionnel

Doit fournir :

* **Symbol Rules** : minQty, stepSize, minNotional, tickSize, precisions
* **Balances** : asset, free, locked
* **Orders** : create, cancel, get status
* **Fills/Trades** : liste des executions avec IDs uniques, fees, timestamps
* **Rate limiting** : signaler limites atteintes, stratégie retry/backoff
* **Health** : ping, latence, statut

### 9quinquies.2 NewsProvider — contrat fonctionnel

Doit fournir :

* id externe + source
* title, content/snippet
* publishedAt, fetchedAt
* language
* url
* entities/tickers (si dispo), sinon brut
* fiabilité (optionnel)

### 9quinquies.3 MarketDataProvider — contrat fonctionnel

Doit fournir :

* tick/last (prix)
* bid/ask (si possible)
* OHLCV par timeframe
* volume
* latency metrics
* quality flags (missing/stale)

---

## 9sexies) Permissions clés API + UX connexion

### 9sexies.1 Scopes minimaux

* Lecture balances/positions
* Trading (spot) si exécution
* **Withdrawals : désactivé** (non requis)

### 9sexies.2 Recommandations

* IP whitelist quand possible
* Rotation périodique

### 9sexies.3 Écran "Connection Check"

* Vérifier :

  * ping OK
  * scopes détectés (si possible)
  * warnings si scopes trop larges
  * test endpoint "read-only"
  * test ordre (optionnel en staging)

---

## 9septies) Multi-portefeuilles (prévu dès V1)

> Même si V1 n'affiche qu'un compte, le modèle doit permettre plusieurs connexions.

### 9septies.1 Concepts

* Un utilisateur peut avoir N **ExchangeConnections**.
* Chaque connection mappe vers un **PortfolioAccount**.
* L'UI peut offrir :

  * un **Global Portfolio** agrégé
  * une vue par compte

### 9septies.2 Impacts modèle

* Toutes les entités portefeuille/ordres/ledger référencent un **AccountId**.
* Les bots sont attachés à un account (ou à un agrégat global, mais recommandé : account).

---

## 10) Sécurité applicative

* MFA
* RBAC (user/admin)
* Protection CSRF (si session)
* Chiffrement secrets
* Redaction des logs (pas de secrets)
* Limites anti-abus (rate limit)
* Kill switch

---

## 11) Roadmap V1 (sans backtest/paper)

### V1 — MVP solide

* Auth + MFA
* Markets + watchlists
* News feed + tags actifs
* Connexion Binance
* Portfolio + ordres + ledger de base
* Bots en mode Conseil + Auto protégé
* Risk Center minimal + kill switch
* Audit + journal bot

### V1.1 — Durcissement

* Reconciliation avancée
* Notifications multi-canaux
* Monitoring plus complet

---

## 12) Critères d'acceptation (Definition of Done)

* Un utilisateur peut connecter Binance, voir son portefeuille, créer un bot, recevoir une proposition, valider, exécuter, puis voir l'ordre et son impact.
* Toute action bot est tracée, explicable, et exportable.
* Les limites de risque stoppent réellement l'exécution.
* Aucune donnée sensible n'apparaît dans les logs.

---

## 13) Glossaire

* **Réserve** : budget isolé attribué à un bot.
* **Proposition / TradePlan** : plan structuré soumis à validation.
* **Ledger** : journal interne de mouvements et calcul P&L.
* **Kill switch** : arrêt immédiat des bots (global / bot).
