# Use Cases Complets - invest.ia

Documentation exhaustive de tous les use cases de la plateforme.

---

## 🟢 1. Base utilisateur

### UC-001: Créer un compte

**Acteur**: Visiteur non authentifié

**Préconditions**: Aucune

**Déclencheur**: L'utilisateur clique sur "S'inscrire"

**Scénario nominal**:
1. L'utilisateur saisit email, mot de passe, prénom, nom
2. Le système valide les données (format email, force mot de passe)
3. Le système crée le compte avec statut "pending_verification"
4. Le système envoie un email de vérification
5. L'utilisateur clique sur le lien dans l'email
6. Le système active le compte
7. L'utilisateur est redirigé vers le dashboard

**Scénarios alternatifs**:
- A1: Email déjà utilisé → erreur "Email déjà enregistré"
- A2: Mot de passe trop faible → erreur avec recommandations
- A3: Email non vérifié après 24h → compte supprimé automatiquement

**Postconditions**: Compte créé et vérifié, utilisateur authentifié

**Implémentation**:
- **Bounded Context**: Identity
- **Command**: `RegisterUser`
- **Handler**: `RegisterUserHandler`
- **Event**: `UserRegistered`, `UserEmailVerified`
- **API**: `POST /api/auth/register`
- **État**: ⚠️ À implémenter

---

### UC-002: Se connecter

**Acteur**: Utilisateur enregistré

**Préconditions**: Compte vérifié

**Déclencheur**: L'utilisateur clique sur "Se connecter"

**Scénario nominal**:
1. L'utilisateur saisit email et mot de passe
2. Le système vérifie les credentials
3. Si MFA activé: demander code MFA
4. Le système génère un JWT token
5. Le système enregistre la session
6. L'utilisateur est redirigé vers le dashboard

**Scénarios alternatifs**:
- A1: Credentials invalides → erreur "Email ou mot de passe incorrect"
- A2: Compte désactivé → erreur "Compte désactivé"
- A3: Trop de tentatives → blocage temporaire (15 min)

**Postconditions**: Session active, token JWT valide

**Implémentation**:
- **Command**: `AuthenticateUser`
- **Handler**: `AuthenticateUserHandler`
- **Event**: `UserLoggedIn`
- **API**: `POST /api/auth/login`
- **État**: ⚠️ À implémenter

---

### UC-003: Se déconnecter

**Acteur**: Utilisateur authentifié

**Scénario nominal**:
1. L'utilisateur clique sur "Se déconnecter"
2. Le système invalide le token JWT
3. Le système termine la session
4. L'utilisateur est redirigé vers la page d'accueil

**Implémentation**:
- **Command**: `LogoutUser`
- **Event**: `UserLoggedOut`
- **API**: `POST /api/auth/logout`
- **État**: ⚠️ À implémenter

---

### UC-004: Activer MFA

**Acteur**: Utilisateur authentifié

**Préconditions**: Compte vérifié, MFA non activé

**Scénario nominal**:
1. L'utilisateur va dans Settings > Sécurité
2. L'utilisateur clique sur "Activer MFA"
3. Le système génère un QR code (TOTP)
4. L'utilisateur scanne avec app authenticator
5. L'utilisateur saisit le code généré pour confirmation
6. Le système valide et active MFA
7. Le système génère codes de récupération

**Implémentation**:
- **Command**: `EnableMfa`
- **Event**: `MfaEnabled`
- **API**: `POST /api/auth/mfa/enable`
- **État**: ⚠️ À implémenter

---

### UC-005: Configurer préférences

**Acteur**: Utilisateur authentifié

**Scénario nominal**:
1. L'utilisateur va dans Settings
2. L'utilisateur modifie:
   - Devise d'affichage (EUR, USD, etc.)
   - Notifications (email, push, telegram)
   - Langue
   - Timezone
3. Le système valide et sauvegarde

**Implémentation**:
- **Command**: `UpdateUserPreferences`
- **API**: `PUT /api/settings/preferences`
- **État**: ⚠️ À implémenter

---

## 🟢 2. Dashboard finance & crypto (lecture)

### UC-006: Voir le dashboard global

**Acteur**: Utilisateur authentifié

**Scénario nominal**:
1. L'utilisateur accède à la page Overview
2. Le système affiche:
   - Market Pulse (indices, BTC, ETH, FX)
   - Valeur portefeuille + P&L
   - Allocation par classe d'actif
   - Cartes des bots (statut, P&L)
   - Watchlists (top movers)
   - News breaking

**Implémentation**:
- **Query**: `GetDashboardOverview`
- **Handler**: `GetDashboardOverviewHandler`
- **API**: `GET /api/dashboard/overview`
- **État**: ⚠️ À implémenter

---

### UC-007: Voir prix et variations d'un actif

**Acteur**: Utilisateur authentifié

**Scénario nominal**:
1. L'utilisateur clique sur un actif (ex: BTC)
2. Le système affiche:
   - Prix actuel (last, bid, ask)
   - Variations (1h, 24h, 7j, 30j)
   - Volume
   - Graphique mini
   - High/Low 24h
   - Timeline unifiée (prix + news + actions bots)

**Implémentation**:
- **Query**: `GetAssetDetail`
- **API**: `GET /api/markets/assets/{symbol}`
- **État**: ⚠️ À implémenter

---

### UC-008: Créer une watchlist

**Acteur**: Utilisateur authentifié

**Scénario nominal**:
1. L'utilisateur clique sur "Nouvelle watchlist"
2. L'utilisateur saisit un nom
3. L'utilisateur sélectionne des actifs (search)
4. Le système crée la watchlist
5. La watchlist apparaît dans le dashboard

**Implémentation**:
- **Command**: `CreateWatchlist`
- **Event**: `WatchlistCreated`
- **API**: `POST /api/markets/watchlists`
- **État**: ⚠️ À implémenter

---

### UC-009: Gérer une watchlist

**Acteur**: Utilisateur authentifié

**Actions possibles**:
- Ajouter un actif
- Retirer un actif
- Renommer
- Supprimer
- Configurer alertes (variation %)

**Implémentation**:
- **Commands**: `AddAssetToWatchlist`, `RemoveAssetFromWatchlist`, etc.
- **API**: `PUT /api/markets/watchlists/{id}`
- **État**: ⚠️ À implémenter

---

### UC-010: Voir news liées aux actifs suivis

**Acteur**: Utilisateur authentifié

**Scénario nominal**:
1. Dans le dashboard, section News
2. Le système affiche news filtrées par:
   - Actifs dans les watchlists
   - Actifs en portefeuille
   - Impact élevé en priorité
3. L'utilisateur peut cliquer pour détail

**Implémentation**:
- **Query**: `GetPersonalizedNewsFeed`
- **API**: `GET /api/news?filter=personalized`
- **État**: ⚠️ À implémenter

---

## 🟡 3. News intelligentes

### UC-011: Consulter flux de news

**Acteur**: Utilisateur authentifié

**Scénario nominal**:
1. L'utilisateur va dans News
2. Le système affiche flux paginé avec:
   - Titre
   - Source
   - Date
   - Tags (actifs concernés)
   - Score d'impact (badge)
   - Sentiment (emoji/couleur)
3. L'utilisateur peut filtrer par:
   - Actif
   - Impact (low/medium/high)
   - Sentiment
   - Source

**Implémentation**:
- **Query**: `GetNewsFeed`
- **API**: `GET /api/news`
- **État**: ⚠️ À implémenter

---

### UC-012: Lire résumé d'une news

**Acteur**: Utilisateur authentifié

**Scénario nominal**:
1. L'utilisateur clique sur une news
2. Le système affiche:
   - Titre complet
   - Résumé clair (3-5 lignes)
   - Source + lien
   - Actifs concernés (avec liens)
   - Score d'impact détaillé
   - Sentiment + justification
   - News similaires

**Implémentation**:
- **Query**: `GetNewsDetail`
- **API**: `GET /api/news/{id}`
- **État**: ⚠️ À implémenter

---

### UC-013: Identifier news importante

**Acteur**: Système (automatique)

**Scénario nominal**:
1. Job ingestion récupère news
2. Service analyse et extrait:
   - Entités (tickers, coins)
   - Mots-clés critiques
   - Sentiment
3. Service calcule score d'impact (0-1) basé sur:
   - Mots-clés (régulation, hack, partnership, etc.)
   - Source fiable
   - Actualité (fraîcheur)
4. Si score > 0.7: marqué "high impact"

**Implémentation**:
- **Service**: `NewsImpactScorer`
- **Event**: `HighImpactNewsDetected`
- **État**: ⚠️ À implémenter

---

### UC-014: Recevoir alerte news impactante

**Acteur**: Utilisateur authentifié (avec notifications actives)

**Préconditions**: News high-impact sur actif suivi ou en portefeuille

**Scénario nominal**:
1. Événement `HighImpactNewsDetected` émis
2. Service vérifie si actif dans watchlist/portfolio utilisateur
3. Service envoie notification:
   - Email
   - Push (si configuré)
   - Telegram (si configuré)
4. L'utilisateur reçoit alerte avec lien direct

**Implémentation**:
- **Handler**: `WhenHighImpactNewsThenNotifyUsers`
- **Service**: `NotificationService`
- **État**: ⚠️ À implémenter

---

## 🟡 4. Connexion argent réel (Binance)

### UC-015: Connecter compte Binance

**Acteur**: Utilisateur authentifié

**Préconditions**: Compte Binance avec API keys créées

**Scénario nominal**:
1. L'utilisateur va dans Settings > Connexions
2. L'utilisateur clique "Ajouter Binance"
3. L'utilisateur saisit:
   - Nom de la connexion
   - API Key
   - API Secret
   - Mode (Production / Testnet)
4. Le système vérifie:
   - Ping OK
   - Permissions (read, trading, NO withdrawal)
   - Test simple (get balances)
5. Si OK: connexion sauvegardée (secrets chiffrés)
6. Sinon: erreur explicite

**Scénarios alternatifs**:
- A1: Permissions insuffisantes → erreur + guide
- A2: Permissions trop larges (withdrawal) → warning
- A3: Ping échoue → erreur connexion

**Implémentation**:
- **Command**: `ConnectExchange`
- **Handler**: `ConnectExchangeHandler`
- **Event**: `ExchangeConnected`
- **Service**: `BinanceAdapter`
- **API**: `POST /api/settings/connections`
- **État**: ⚠️ À implémenter

---

### UC-016: Vérifier connexion

**Acteur**: Utilisateur authentifié

**Scénario nominal**:
1. Dans Settings > Connexions
2. L'utilisateur clique "Tester connexion"
3. Le système exécute:
   - Ping
   - Get server time (check latence)
   - Get account info
   - Check rate limits
4. Affiche résultat + santé (healthy/degraded/down)

**Implémentation**:
- **Query**: `CheckExchangeHealth`
- **API**: `GET /api/settings/connections/{id}/health`
- **État**: ⚠️ À implémenter

---

### UC-017: Voir portefeuille réel

**Acteur**: Utilisateur authentifié avec connexion active

**Scénario nominal**:
1. L'utilisateur va dans Portfolio
2. Le système:
   - Récupère balances de Binance
   - Récupère prix actuels
   - Calcule valeurs en quote + reporting currency
3. Affiche:
   - Liste des positions (asset, quantité, valeur)
   - Total value
   - P&L (réalisé + non réalisé)
   - Allocation (pie chart)

**Implémentation**:
- **Query**: `GetPortfolioSnapshot`
- **Service**: `BinanceAdapter.getBalances()`
- **API**: `GET /api/portfolio/accounts/{id}`
- **État**: ⚠️ À implémenter

---

### UC-018: Voir historique ordres

**Acteur**: Utilisateur authentifié

**Scénario nominal**:
1. L'utilisateur va dans Portfolio > Ordres
2. Le système affiche liste paginée:
   - Date/heure
   - Symbole
   - Type (buy/sell)
   - Quantité
   - Prix
   - Statut (filled/cancelled/failed)
   - P&L (pour ordres complétés)
3. Filtres: date, symbole, statut, bot

**Implémentation**:
- **Query**: `GetOrderHistory`
- **API**: `GET /api/trading/orders`
- **État**: ⚠️ À implémenter

---

## 🟠 5. Bots (création & configuration)

### UC-019: Créer un bot

**Acteur**: Utilisateur authentifié avec connexion exchange active

**Scénario nominal**:
1. L'utilisateur clique "Nouveau bot"
2. L'utilisateur configure:
   - Nom du bot
   - Compte associé (dropdown)
   - Mode (Conseil / Auto protégé / Auto)
   - Horizon (Court / Moyen / Long)
   - Univers d'actifs (sélection multiple)
   - Réserve (montant en USDT ou autre)
3. L'utilisateur configure règles de risque:
   - Taille max position (% réserve)
   - Nb max positions simultanées
   - Perte max par jour (montant)
   - Exposition max par actif (%)
4. Le système valide:
   - Réserve disponible suffisante
   - Règles cohérentes
5. Bot créé avec statut "PAUSED"

**Implémentation**:
- **Command**: `CreateBot`
- **Handler**: `CreateBotHandler`
- **Event**: `BotCreated`
- **API**: `POST /api/bots`
- **État**: ⚠️ À implémenter

---

### UC-020: Choisir univers d'investissement

**Acteur**: Utilisateur lors de création/modification bot

**Options**:
- Top N crypto (ex: Top 20 par market cap)
- Sélection manuelle (liste de symboles)
- Catégorie (DeFi, Layer1, Meme, etc.)
- Watchlist existante

**Implémentation**:
- Champ `universe` dans configuration bot
- Validation que symboles existent et sont tradables
- **État**: ⚠️ À implémenter

---

### UC-021: Choisir horizon

**Acteur**: Utilisateur lors de création/modification bot

**Options**:
- **Court terme**: minutes/heures, faible time-in-market, scalping
- **Moyen terme**: jours/semaines, swing trading
- **Long terme**: semaines/mois, accumulation, DCA

**Impact**:
- Court: décisions rapides, stop-loss serrés
- Moyen: patience, moins de trades
- Long: hold, moins sensible volatilité court terme

**Implémentation**:
- Enum `BotHorizon`: SHORT, MEDIUM, LONG
- **État**: ⚠️ À implémenter

---

### UC-022: Allouer budget au bot

**Acteur**: Utilisateur lors de création/modification bot

**Scénario nominal**:
1. L'utilisateur saisit montant de réserve
2. Le système vérifie:
   - Balance disponible >= montant
   - Réserve pas déjà allouée à autre bot
3. Si OK: réserve créée et allouée
4. Le montant est "verrouillé" pour ce bot

**Règles**:
- Un montant ne peut être dans plusieurs réserves
- Réserve peut être ajustée (augmentée/diminuée)
- Si diminuée: vérifier pas de positions > nouvelle réserve

**Implémentation**:
- Entité `BotReserve`
- Validation dans `CreateBotHandler`
- **État**: ⚠️ À implémenter

---

### UC-023: Démarrer un bot

**Acteur**: Utilisateur authentifié

**Préconditions**: Bot en statut PAUSED

**Scénario nominal**:
1. L'utilisateur clique "Démarrer"
2. Le système vérifie:
   - Connexion exchange active
   - Réserve disponible
   - Règles valides
3. Bot passe en statut RUNNING
4. Tick marché commence à s'exécuter

**Implémentation**:
- **Command**: `StartBot`
- **Event**: `BotStarted`
- **API**: `POST /api/bots/{id}/start`
- **État**: ⚠️ À implémenter

---

### UC-024: Mettre en pause un bot

**Acteur**: Utilisateur authentifié

**Scénario nominal**:
1. L'utilisateur clique "Pause"
2. Bot passe en statut PAUSED
3. Ticks arrêtés
4. Positions actuelles conservées (pas de vente forcée)
5. Aucune nouvelle proposition

**Implémentation**:
- **Command**: `PauseBot`
- **Event**: `BotPaused`
- **API**: `POST /api/bots/{id}/pause`
- **État**: ⚠️ À implémenter

---

### UC-025: Relancer un bot

**Acteur**: Utilisateur authentifié

**Préconditions**: Bot PAUSED

**Scénario nominal**:
1. L'utilisateur clique "Relancer"
2. Vérifications (même que démarrage)
3. Bot repasse RUNNING
4. Ticks reprennent

**Implémentation**:
- Même commande que UC-023: `StartBot`
- **État**: ⚠️ À implémenter

---

## 🟠 6. Analyse & propositions (avant mise)

### UC-026: Tick marché - Observer le marché

**Acteur**: Système (job schedulé)

**Déclencheur**: Cron job (ex: toutes les 5 minutes pour bots court terme)

**Scénario nominal**:
1. Job `BotMarketTick` s'exécute
2. Pour chaque bot RUNNING:
   - Récupère prix actifs dans univers
   - Récupère indicateurs techniques (optionnel V1)
   - Récupère news récentes high-impact
3. Appelle service `OpportunityDetector`

**Implémentation**:
- **Job**: `BotMarketTickCommand` (Symfony Console)
- **Service**: `BotOrchestrator`
- **Messenger**: Message asynchrone par bot
- **État**: ⚠️ À implémenter

---

### UC-027: Détecter une opportunité

**Acteur**: Service bot (via tick marché)

**Scénario nominal**:
1. Service `OpportunityDetector` analyse:
   - Variations de prix significatives
   - News high-impact positives
   - Sentiment marché
   - Corrélations (optionnel)
2. Si pattern détecté (ex: support fort + news positive):
   - Générer signal "BUY opportunity"
3. Passe au service d'évaluation risque

**Règles de détection (exemples V1)**:
- Prix touche support fort ET news positive
- Volume anormal + sentiment positif
- Baisse > X% sans raison (opportunité achat)

**Implémentation**:
- **Service**: `OpportunityDetector`
- **Domain Event**: `OpportunityDetected`
- **État**: ⚠️ À implémenter

---

### UC-028: Évaluer le risque

**Acteur**: Service bot

**Scénario nominal**:
1. Service `RiskEvaluator` analyse:
   - Volatilité actif (dernières 24h)
   - Corrélation portefeuille existant
   - Taille position vs réserve
   - Exposition actuelle sur cet actif
   - News négatives récentes
2. Calcule score de risque: LOW / MEDIUM / HIGH
3. Si risque acceptable: continue vers proposition

**Règles**:
- HIGH si volatilité > 10% en 24h
- HIGH si déjà exposé > 30% réserve sur cet actif
- MEDIUM si volatilité 5-10%
- LOW si < 5%

**Implémentation**:
- **Service**: `RiskEvaluator`
- **État**: ⚠️ À implémenter

---

### UC-029: Expliquer la décision

**Acteur**: Service bot

**Scénario nominal**:
1. Service `DecisionExplainer` génère texte clair:
   - **Quoi**: "Achat 0.05 BTC (~2250 USDT)"
   - **Pourquoi**: "Prix touche support 44500 + news positive (ETF approval)"
   - **Risque**: "Moyen (volatilité 7% en 24h)"
   - **Invalidation**: "Si prix < 44000"
   - **Objectif**: "Take profit à 46000 (+3.3%)"
2. Texte stocké dans proposition

**Implémentation**:
- **Service**: `DecisionExplainer`
- Peut utiliser templates + variables
- **État**: ⚠️ À implémenter

---

### UC-030: Proposer un investissement

**Acteur**: Service bot

**Scénario nominal**:
1. Service `ProposalGenerator` crée `TradePlan`:
   - Symbole
   - Côté (BUY/SELL)
   - Quantité estimée
   - Prix estimé
   - Type ordre (MARKET/LIMIT)
   - Justification (texte de UC-029)
   - Risque (LOW/MEDIUM/HIGH)
   - État: PENDING_APPROVAL
   - Expiration (ex: 30 minutes)
2. TradePlan sauvegardé
3. Événement `DecisionProposed` émis
4. Notification envoyée à utilisateur (selon mode)

**Règles selon mode**:
- **Conseil**: toujours PENDING_APPROVAL
- **Auto protégé**: PENDING_APPROVAL si risque MEDIUM/HIGH ou confiance < seuil
- **Auto**: approbation automatique (passage direct à exécution)

**Implémentation**:
- **Service**: `ProposalGenerator`
- **Entity**: `TradePlan`
- **Event**: `DecisionProposed`
- **API**: Notification → `/api/bots/{id}/proposals`
- **État**: ⚠️ À implémenter

---

### UC-031: Accepter une proposition

**Acteur**: Utilisateur authentifié

**Préconditions**: Proposition en PENDING_APPROVAL

**Scénario nominal**:
1. L'utilisateur voit notification "Nouvelle proposition"
2. L'utilisateur consulte détails
3. L'utilisateur clique "Approuver"
4. Le système:
   - Change état TradePlan → APPROVED
   - Émet événement `DecisionApproved`
   - Lance processus d'exécution (UC-034)

**Implémentation**:
- **Command**: `ApproveProposal`
- **Event**: `DecisionApproved`
- **API**: `POST /api/bots/proposals/{id}/approve`
- **État**: ⚠️ À implémenter

---

### UC-032: Refuser une proposition

**Acteur**: Utilisateur authentifié

**Scénario nominal**:
1. L'utilisateur clique "Rejeter"
2. (Optionnel) L'utilisateur saisit raison
3. Le système:
   - Change état TradePlan → REJECTED
   - Émet événement `DecisionRejected`
   - Proposition archivée

**Implémentation**:
- **Command**: `RejectProposal`
- **Event**: `DecisionRejected`
- **API**: `POST /api/bots/proposals/{id}/reject`
- **État**: ⚠️ À implémenter

---

### UC-033: Expiration d'une proposition

**Acteur**: Système (job schedulé)

**Déclencheur**: Temps d'expiration atteint

**Scénario nominal**:
1. Job vérifie propositions avec `expiresAt` < now
2. Pour chaque proposition expirée:
   - Change état → EXPIRED
   - Émet événement `DecisionExpired`
3. Proposition ne peut plus être approuvée

**Implémentation**:
- **Job**: `ExpireOldProposalsCommand`
- **Event**: `DecisionExpired`
- **État**: ⚠️ À implémenter

---

## 🔵 7. Exécution réelle (contrôlée)

### UC-034: Transformer proposition en ordre

**Acteur**: Système (suite à approbation)

**Préconditions**: TradePlan APPROVED

**Scénario nominal**:
1. Service `OrderExecutor` reçoit événement `DecisionApproved`
2. Service crée entité `Order`:
   - Récupère symbol rules (step size, tick size, minQty)
   - Normalise quantité (arrondi DOWN)
   - Normalise prix (selon type ordre)
   - Génère idempotencyKey
   - État: DRAFT
3. Service effectue contrôles pré-exécution:
   - Vérifier réserve suffisante
   - Vérifier limites risque pas dépassées
   - Vérifier minQty / minNotional respectés
4. Si OK: continue vers envoi
5. Sinon: Order → FAILED

**Implémentation**:
- **Service**: `OrderExecutor`
- **Handler**: `WhenDecisionApprovedThenCreateOrder`
- **Entity**: `Order`
- **État**: ⚠️ À implémenter

---

### UC-035: Envoyer ordre à Binance

**Acteur**: Système

**Préconditions**: Order DRAFT avec contrôles OK

**Scénario nominal**:
1. Service `OrderExecutor` appelle `BinanceAdapter.createOrder()`
2. Adapter envoie requête POST à Binance
3. Binance répond:
   - orderId exchange
   - statut initial
   - timestamp
4. Service met à jour Order:
   - exchangeOrderId
   - État: SENT
   - sentAt
5. Événement `OrderSubmitted` émis

**Scénarios alternatifs**:
- A1: Rate limit → retry avec backoff
- A2: Erreur validation Binance → Order FAILED
- A3: Erreur réseau → retry (circuit breaker si trop d'échecs)

**Implémentation**:
- **Service**: `BinanceAdapter.createOrder()`
- **Event**: `OrderSubmitted`
- **État**: ⚠️ À implémenter

---

### UC-036: Suivre l'exécution

**Acteur**: Système (job ou webhook)

**Deux approches**:

**A) Polling (V1)**:
1. Job `SyncOrderStatus` s'exécute (ex: toutes les 30s)
2. Pour chaque ordre SENT ou PART_FILLED:
   - Appelle `BinanceAdapter.getOrder()`
   - Compare statut
   - Si changement: met à jour

**B) User Data Stream (V2)**:
1. Connexion websocket à Binance
2. Réception événements temps réel
3. Traitement immédiat

**Implémentation**:
- **Job**: `SyncOrderStatusCommand`
- **Service**: `OrderStatusSynchronizer`
- **État**: ⚠️ À implémenter (polling V1)

---

### UC-037: Ordre partiellement exécuté

**Acteur**: Système (suite sync status)

**Scénario nominal**:
1. Statut Binance = PARTIALLY_FILLED
2. Service récupère fills:
   - tradeId
   - prix
   - quantité
   - fee
3. Pour chaque nouveau fill:
   - Créer `OrderFill` (déduplication par tradeId)
   - Mettre à jour `executedQuantity` sur Order
4. Ordre passe en état PART_FILLED
5. Événement `OrderPartiallyFilled` émis

**Implémentation**:
- **Entity**: `OrderFill`
- **Event**: `OrderPartiallyFilled`
- **État**: ⚠️ À implémenter

---

### UC-038: Ordre complètement exécuté

**Acteur**: Système (suite sync status)

**Scénario nominal**:
1. Statut Binance = FILLED
2. Tous les fills récupérés et stockés
3. Ordre passe en état FILLED
4. Événement `OrderFilled` émis
5. Déclenche mise à jour portefeuille (UC-039)

**Implémentation**:
- **Event**: `OrderFilled`
- **État**: ⚠️ À implémenter

---

### UC-039: Mettre à jour le portefeuille

**Acteur**: Système

**Déclencheur**: `OrderFilled` ou `OrderPartiallyFilled`

**Scénario nominal**:
1. Handler reçoit événement avec fills
2. Pour chaque fill:
   - Créer entrées ledger (double-entry):
     * Débit quote asset (ex: -4500 USDT)
     * Crédit base asset (ex: +0.1 BTC)
     * Débit fee asset (ex: -0.00001 BTC)
3. Mettre à jour Position:
   - Si nouveau: créer Position
   - Si existant: ajuster quantité et coût moyen
4. Mettre à jour Balance:
   - free / locked
5. Événement `BalanceUpdated`, `PositionOpened/Updated`

**Règles FIFO**:
- Pour BUY: ajouter lot avec prix et quantité
- Pour SELL: matcher avec lots existants (FIFO)

**Implémentation**:
- **Handler**: `WhenOrderFilledThenUpdatePortfolio`
- **Service**: `PortfolioManager`, `LedgerService`
- **État**: ⚠️ À implémenter

---

### UC-040: Ordre annulé

**Acteur**: Utilisateur ou système

**Scénario nominal**:
1. Ordre SENT ou PART_FILLED
2. Appel `BinanceAdapter.cancelOrder()`
3. Binance confirme annulation
4. Ordre passe en état CANCELLED
5. Si fills partiels: portefeuille déjà mis à jour

**Implémentation**:
- **Command**: `CancelOrder`
- **Event**: `OrderCancelled`
- **API**: `DELETE /api/trading/orders/{id}`
- **État**: ⚠️ À implémenter

---

### UC-041: Ordre échoué

**Acteur**: Système

**Causes**:
- Validation Binance échouée
- Fonds insuffisants
- Erreur technique

**Scénario nominal**:
1. Ordre passe en état FAILED
2. Erreur loggée
3. Événement `OrderFailed` émis
4. Notification utilisateur
5. Pas de mise à jour portefeuille

**Implémentation**:
- **Event**: `OrderFailed`
- **État**: ⚠️ À implémenter

---

## 🔵 8. Suivi après mise (ticks de suivi)

### UC-042: Tick de suivi - Surveiller position

**Acteur**: Système (job schedulé)

**Déclencheur**: Cron job (ex: toutes les 10 minutes)

**Scénario nominal**:
1. Job `BotPositionMonitoringTick` s'exécute
2. Pour chaque bot RUNNING avec positions ouvertes:
   - Récupère positions actives
   - Pour chaque position:
     * Récupère prix actuel
     * Calcule P&L non réalisé
     * Vérifie invalidation (ex: stop-loss)
     * Vérifie news impactantes
     * Évalue si thèse toujours valide
3. Appelle service `PositionMonitor`

**Implémentation**:
- **Job**: `BotPositionMonitoringTickCommand`
- **Service**: `PositionMonitor`
- **État**: ⚠️ À implémenter

---

### UC-043: Recalculer P&L et statut

**Acteur**: Service bot (via tick suivi)

**Scénario nominal**:
1. Service récupère:
   - Coût moyen position
   - Quantité actuelle
   - Prix actuel
2. Calcule:
   - P&L unrealised = (prix actuel - coût moyen) × quantité
   - P&L % = (prix actuel / coût moyen - 1) × 100
3. Met à jour Position avec nouveau P&L
4. Événement `PositionPnLUpdated` (si changement significatif)

**Implémentation**:
- **Service**: `PnLCalculator`
- **État**: ⚠️ À implémenter

---

### UC-044: Vérifier thèse toujours valide

**Acteur**: Service bot

**Scénario nominal**:
1. Service récupère TradePlan original (justification)
2. Vérifie conditions d'invalidation:
   - Prix < stop-loss défini
   - News négative high-impact apparue
   - Changement sentiment marché
   - Temps max détention dépassé (selon horizon)
3. Si invalidée: génère signal "EXIT"
4. Sinon: vérifie si objectif atteint (take-profit)

**Implémentation**:
- **Service**: `ThesisValidator`
- **État**: ⚠️ À implémenter

---

### UC-045: Détecter news impactant position

**Acteur**: Service bot

**Scénario nominal**:
1. Service récupère news récentes (dernière heure)
2. Filtre par symboles en portefeuille
3. Si news high-impact ET sentiment négatif:
   - Générer alerte
   - Marquer position "À SURVEILLER"
4. Peut déclencher proposition de sortie (UC-048)

**Implémentation**:
- **Service**: `NewsImpactAnalyzer`
- **Event**: `NewsImpactingPositionDetected`
- **État**: ⚠️ À implémenter

---

### UC-046: Mettre à jour état position

**Acteur**: Service bot

**États possibles**:
- **OK**: P&L positif ou légèrement négatif, pas de news négative
- **À SURVEILLER**: P&L négatif > -5% OU news négative
- **RISQUÉ**: P&L négatif > -10% OU news très négative OU invalidation

**Scénario nominal**:
1. Service évalue critères
2. Assigne état approprié
3. Si changement état: événement `PositionStateChanged`
4. Si RISQUÉ: peut déclencher notification immédiate

**Implémentation**:
- **Enum**: `PositionState`
- **Event**: `PositionStateChanged`
- **État**: ⚠️ À implémenter

---

## 🔴 9. Actions pendant le suivi

### UC-047: Informer utilisateur d'un changement

**Acteur**: Système (suite détection changement)

**Déclencheur**: Position passe en RISQUÉ ou news impactante

**Scénario nominal**:
1. Handler reçoit événement (ex: `PositionStateChanged`)
2. Service génère notification:
   - Titre: "Position BTC à surveiller"
   - Message: résumé (P&L, raison)
   - Lien vers détail position
3. Notification envoyée selon préférences utilisateur
4. Badge "nouveau" dans UI

**Implémentation**:
- **Handler**: `WhenPositionRiskyThenNotifyUser`
- **Service**: `NotificationService`
- **État**: ⚠️ À implémenter

---

### UC-048: Bot propose de sortir

**Acteur**: Service bot

**Préconditions**: Position invalidée ou risque élevé

**Scénario nominal**:
1. Service génère TradePlan de sortie:
   - Côté: SELL
   - Quantité: quantité totale position
   - Justification: "Thèse invalidée: prix < stop-loss"
   - Type: MARKET ou LIMIT selon urgence
2. TradePlan créé avec état PENDING_APPROVAL
3. Événement `ExitProposed` émis
4. Notification utilisateur (priorité haute)

**Implémentation**:
- **Service**: `ExitProposalGenerator`
- **Event**: `ExitProposed`
- **État**: ⚠️ À implémenter

---

### UC-049: Bot propose de réduire

**Acteur**: Service bot

**Préconditions**: Position à surveiller mais pas critique

**Scénario nominal**:
1. Service propose vente partielle (ex: 50%)
2. TradePlan:
   - Côté: SELL
   - Quantité: 50% position actuelle
   - Justification: "Sécuriser gains partiels"
3. Même workflow qu'UC-048

**Implémentation**:
- **Service**: `PartialExitProposalGenerator`
- **État**: ⚠️ À implémenter

---

### UC-050: Bot propose de ne rien faire

**Acteur**: Service bot

**Scénario**:
1. Évaluation montre: position OK, thèse valide
2. Pas de proposition générée
3. Monitoring continue
4. Log pour audit: "No action needed"

**Implémentation**:
- Logique dans `PositionMonitor`
- **État**: ⚠️ À implémenter

---

### UC-051: Valider action de sortie

**Acteur**: Utilisateur authentifié

**Scénario nominal**:
1. Utilisateur reçoit notification "Proposition de sortie"
2. Utilisateur consulte détails
3. Utilisateur approuve ou rejette
4. Si approuvé: même workflow exécution qu'UC-034 à UC-039

**Implémentation**:
- Même que UC-031 (`ApproveProposal`)
- **État**: ⚠️ À implémenter

---

### UC-052: Exécuter sortie automatique (urgence)

**Acteur**: Système (cas extrême)

**Préconditions**: Perte critique OU kill switch activé

**Scénario nominal**:
1. Détection perte > seuil critique (ex: -15%)
2. Système génère ordre MARKET immédiat
3. Pas d'attente validation utilisateur
4. Ordre exécuté
5. Notification post-exécution
6. Bot passe en HALT

**Règles**:
- Uniquement si mode Auto OU limite globale atteinte
- Toujours journalisé avec justification

**Implémentation**:
- **Service**: `EmergencyExitExecutor`
- **Event**: `EmergencyExitExecuted`
- **État**: ⚠️ À implémenter

---

## 🔴 10. Sécurité & limites (ticks inclus)

### UC-053: Tick risque - Vérifier limites

**Acteur**: Système (job schedulé)

**Déclencheur**: Cron job (ex: toutes les 5 minutes)

**Scénario nominal**:
1. Job `RiskMonitoringTick` s'exécute
2. Pour chaque bot RUNNING:
   - Calcule perte journalière cumulée
   - Calcule exposition par actif
   - Calcule exposition totale
   - Vérifie vs limites définies
3. Si limite dépassée: déclenche action

**Implémentation**:
- **Job**: `RiskMonitoringTickCommand`
- **Service**: `RiskMonitor`
- **État**: ⚠️ À implémenter

---

### UC-054: Limites globales

**Acteur**: Administrateur ou utilisateur

**Limites configurables**:
- Perte max journalière globale
- Perte max hebdomadaire
- Exposition max totale
- Exposition max par actif
- Nombre max positions simultanées

**Implémentation**:
- **Entity**: `RiskLimit`
- **API**: `PUT /api/risk/limits`
- **État**: ⚠️ À implémenter

---

### UC-055: Limites par bot

**Acteur**: Utilisateur lors configuration bot

**Limites configurables**:
- Perte max par jour
- Perte max par trade
- Taille max position (% réserve)
- Exposition max par actif

**Implémentation**:
- **Entity**: `BotRuleSet`
- **État**: ⚠️ À implémenter

---

### UC-056: Arrêt automatique si limite atteinte

**Acteur**: Système (suite tick risque)

**Scénario nominal**:
1. Détection perte journalière > limite
2. Système:
   - Passe bot en état HALT
   - Annule ordres ouverts (si possible)
   - Génère propositions sortie pour positions ouvertes
   - Émet événement `RiskLimitBreached`
   - Notification utilisateur (priorité critique)
3. Bot ne peut redémarrer qu'après:
   - Validation utilisateur
   - Ou reset automatique (lendemain pour limite journalière)

**Implémentation**:
- **Handler**: `WhenRiskLimitBreachedThenHaltBot`
- **Event**: `RiskLimitBreached`
- **État**: ⚠️ À implémenter

---

### UC-057: Kill switch manuel global

**Acteur**: Utilisateur authentifié

**Scénario nominal**:
1. L'utilisateur va dans Risk Center
2. L'utilisateur clique "Activer Kill Switch Global"
3. Confirmation requise (saisir raison)
4. Système:
   - Passe TOUS les bots en HALT
   - Annule tous ordres ouverts
   - Émet événement `KillSwitchActivated`
   - Aucun bot ne peut redémarrer
5. Notification confirmant action

**Implémentation**:
- **Command**: `ActivateKillSwitch`
- **Event**: `KillSwitchActivated`
- **API**: `POST /api/risk/kill-switch`
- **État**: ⚠️ À implémenter

---

### UC-058: Kill switch par bot

**Acteur**: Utilisateur authentifié

**Scénario nominal**:
1. Depuis détail bot
2. Clic "Arrêt d'urgence"
3. Bot passe immédiatement en HALT
4. Ordres annulés
5. Positions conservées (pas de vente forcée sauf si configuré)

**Implémentation**:
- **Command**: `HaltBot`
- **Event**: `BotHalted`
- **API**: `POST /api/bots/{id}/halt`
- **État**: ⚠️ À implémenter

---

### UC-059: Désactiver kill switch

**Acteur**: Utilisateur authentifié

**Scénario nominal**:
1. Dans Risk Center
2. Clic "Désactiver Kill Switch"
3. Système:
   - Désactive kill switch
   - Bots restent HALT (pas de redémarrage auto)
   - Émet événement `KillSwitchDeactivated`
4. Utilisateur peut redémarrer bots manuellement

**Implémentation**:
- **Command**: `DeactivateKillSwitch`
- **Event**: `KillSwitchDeactivated`
- **API**: `DELETE /api/risk/kill-switch`
- **État**: ⚠️ À implémenter

---

## 🟣 11. Historique & transparence

### UC-060: Voir historique décisions bot

**Acteur**: Utilisateur authentifié

**Scénario nominal**:
1. L'utilisateur va dans détail bot > Journal
2. Le système affiche timeline:
   - Date/heure
   - Type (analyse / proposition / exécution)
   - Détails (symbole, quantité, prix)
   - Justification
   - Décision utilisateur (approuvé/rejeté)
   - Résultat (si exécuté)
3. Filtres: date, type, symbole

**Implémentation**:
- **Query**: `GetBotDecisionHistory`
- **API**: `GET /api/bots/{id}/decisions`
- **État**: ⚠️ À implémenter

---

### UC-061: Voir historique trades

**Acteur**: Utilisateur authentifié

**Scénario nominal**:
1. Dans Portfolio > Historique
2. Affiche tous les trades:
   - Date/heure
   - Symbole
   - Type (buy/sell)
   - Quantité
   - Prix
   - Fees
   - P&L (pour sell)
   - Bot responsable (si applicable)
3. Export CSV possible

**Implémentation**:
- **Query**: `GetTradeHistory`
- **API**: `GET /api/portfolio/trades`
- **État**: ⚠️ À implémenter

---

### UC-062: Comprendre pourquoi décision prise

**Acteur**: Utilisateur authentifié

**Scénario nominal**:
1. Dans historique décisions
2. Clic sur une décision
3. Affiche détail complet:
   - Contexte marché au moment T
   - Signaux détectés
   - News considérées
   - Évaluation risque
   - Justification complète
   - Règles appliquées
4. Timeline "avant/pendant/après"

**Implémentation**:
- **Query**: `GetDecisionDetail`
- **API**: `GET /api/bots/decisions/{id}`
- **État**: ⚠️ À implémenter

---

### UC-063: Comprendre pourquoi position fermée

**Acteur**: Utilisateur authentifié

**Scénario nominal**:
1. Dans historique positions
2. Clic sur position fermée
3. Affiche:
   - Raison fermeture (thèse invalidée / objectif atteint / limite risque / manuel)
   - Conditions au moment fermeture
   - P&L final
   - Durée détention
   - Timeline complète (entrée → suivi → sortie)

**Implémentation**:
- **Query**: `GetPositionLifecycle`
- **API**: `GET /api/portfolio/positions/{id}/lifecycle`
- **État**: ⚠️ À implémenter

---

### UC-064: Export audit trail

**Acteur**: Utilisateur authentifié ou admin

**Scénario nominal**:
1. Dans Settings ou section Audit
2. Sélection période
3. Sélection scope (bot spécifique / global)
4. Génération export:
   - CSV ou PDF
   - Tous événements
   - Tous ordres
   - Tous P&L
5. Téléchargement fichier

**Implémentation**:
- **Command**: `GenerateAuditExport`
- **Service**: `AuditExporter`
- **API**: `POST /api/audit/export`
- **État**: ⚠️ À implémenter

---

## 📊 Résumé d'implémentation

| Catégorie | Use Cases | Statut V1 |
|-----------|-----------|-----------|
| Base utilisateur (UC-001 à UC-005) | 5 | ⚠️ À implémenter |
| Dashboard & Markets (UC-006 à UC-010) | 5 | ⚠️ À implémenter |
| News (UC-011 à UC-014) | 4 | ⚠️ À implémenter |
| Connexion Binance (UC-015 à UC-018) | 4 | ⚠️ À implémenter |
| Bots config (UC-019 à UC-025) | 7 | ⚠️ À implémenter |
| Analyse & propositions (UC-026 à UC-033) | 8 | ⚠️ À implémenter |
| Exécution (UC-034 à UC-041) | 8 | ⚠️ À implémenter |
| Suivi positions (UC-042 à UC-046) | 5 | ⚠️ À implémenter |
| Actions suivi (UC-047 à UC-052) | 6 | ⚠️ À implémenter |
| Sécurité & limites (UC-053 à UC-059) | 7 | ⚠️ À implémenter |
| Historique (UC-060 à UC-064) | 5 | ⚠️ À implémenter |
| **TOTAL** | **64** | **0% implémenté** |

---

## 🎯 Ordre d'implémentation recommandé (Sprints)

### Sprint 1: Fondations
- UC-001 à UC-005 (Base utilisateur)
- UC-015 (Connexion Binance basique)

### Sprint 2: Lecture données
- UC-006 à UC-010 (Dashboard & Markets)
- UC-016 à UC-018 (Portfolio lecture)

### Sprint 3: News
- UC-011 à UC-014 (News intelligentes)

### Sprint 4: Bots - Config
- UC-019 à UC-025 (Création et gestion bots)

### Sprint 5: Bots - Analyse
- UC-026 à UC-030 (Tick marché et détection)
- UC-031 à UC-033 (Validation propositions)

### Sprint 6: Exécution
- UC-034 à UC-041 (Envoi ordres et suivi)

### Sprint 7: Suivi positions
- UC-042 à UC-046 (Tick suivi)
- UC-047 à UC-050 (Propositions sortie)

### Sprint 8: Sécurité
- UC-053 à UC-059 (Tick risque et kill switch)

### Sprint 9: Transparence
- UC-060 à UC-064 (Historique et audit)

### Sprint 10: Polish & Tests
- Tests end-to-end
- Performance
- UX/UI final
