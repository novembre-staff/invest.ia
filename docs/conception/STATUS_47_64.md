# 📊 État actuel: 50/64 Use Cases (78.1%)

**Date**: 16 Décembre 2025  
**Progression**: ✅ 50/64 UC complétés (78.1%)  
**Fichiers**: 302 fichiers PHP (+20)  
**Tests**: 32 fichiers de tests (+2)

---

## 📦 Architecture actuelle

### Bounded Contexts implémentés (12 controllers)

1. ✅ **Identity** - AuthController, SettingsController
2. ✅ **Exchange** - ExchangeController  
3. ✅ **Market** - MarketController
4. ✅ **Portfolio** - PortfolioController
5. ✅ **News** - NewsController
6. ✅ **Alert** - AlertController
7. ✅ **Trading** - OrderController
8. ✅ **Strategy** - StrategyController (Bots)
9. ✅ **Risk** - RiskController
10. ✅ **Automation** - AutomationController
11. ✅ **Analytics** - AnalyticsController
12. ⚠️ **Audit** - (à compléter)

### Domain Models (14 aggregates)

1. User (Identity)
2. ExchangeConnection (Exchange)
3. Watchlist, MarketData (Market)
4. Portfolio, Trade (Portfolio)
5. NewsArticle (News)
6. PriceAlert (Alert)
7. Order (Trading)
8. TradingStrategy (Strategy/Bots)
9. RiskProfile, RiskAssessment (Risk)
10. Automation (Automation)
11. PerformanceReport (Analytics)

---

## ✅ Use Cases complétés (47/64)

### 🟢 1. Base utilisateur (5/5) ✅
- ✅ UC-001: Créer un compte
- ✅ UC-002: Se connecter
- ✅ UC-003: Se déconnecter
- ✅ UC-004: Activer MFA
- ✅ UC-005: Configurer préférences

### 🟢 2. Dashboard & Markets (5/5) ✅
- ✅ UC-006: Voir dashboard global
- ✅ UC-007: Voir prix et variations d'un actif
- ✅ UC-008: Créer une watchlist
- ✅ UC-009: Gérer une watchlist
- ✅ UC-010: Voir news liées aux actifs suivis

### 🟡 3. News intelligentes (4/4) ✅
- ✅ UC-011: Consulter flux de news
- ✅ UC-012: Lire résumé d'une news
- ✅ UC-013: Identifier news importante
- ✅ UC-014: Recevoir alerte news impactante

### 🟡 4. Connexion Binance (4/4) ✅
- ✅ UC-015: Connecter compte Binance
- ✅ UC-016: Vérifier connexion
- ✅ UC-017: Voir portefeuille réel
- ✅ UC-018: Voir historique ordres

### 🟠 5. Bots - Configuration (7/7) ✅
- ✅ UC-019: Créer un bot
- ✅ UC-020: Choisir univers d'investissement
- ✅ UC-021: Choisir horizon
- ✅ UC-022: Allouer budget au bot
- ✅ UC-023: Démarrer un bot
- ✅ UC-024: Mettre en pause un bot
- ✅ UC-025: Relancer un bot

### 🟠 6. Analyse & Propositions (7/8) ⚠️
- ✅ UC-026: Tick marché - Observer le marché
- ✅ UC-027: Détecter une opportunité
- ✅ UC-028: Évaluer le risque
- ⚠️ UC-029: Expliquer la décision (partiel)
- ⚠️ UC-030: Proposer un investissement (partiel)
- ✅ UC-031: Accepter une proposition
- ✅ UC-032: Refuser une proposition
- ✅ UC-033: Expiration d'une proposition

### 🔵 7. Exécution réelle (6/8) ⚠️
- ✅ UC-034: Transformer proposition en ordre
- ✅ UC-035: Envoyer ordre à Binance
- ✅ UC-036: Suivre l'exécution
- ✅ UC-037: Ordre partiellement exécuté
- ✅ UC-038: Ordre complètement exécuté
- ✅ UC-039: Mettre à jour le portefeuille
- ❌ UC-040: Ordre annulé
- ❌ UC-041: Ordre échoué

### 🔵 8. Suivi positions (3/5) ⚠️
- ✅ UC-042: Tick de suivi - Surveiller position
- ✅ UC-043: Recalculer P&L et statut
- ❌ UC-044: Vérifier thèse toujours valide
- ❌ UC-045: Détecter news impactant position
- ✅ UC-046: Mettre à jour état position

### 🔴 9. Actions pendant suivi (2/6) ⚠️
- ✅ UC-047: Informer utilisateur d'un changement
- ❌ UC-048: Bot propose de sortir
- ❌ UC-049: Bot propose de réduire
- ❌ UC-050: Bot propose de ne rien faire
- ❌ UC-051: Valider action de sortie
- ❌ UC-052: Exécuter sortie automatique (urgence)

### 🔴 10. Sécurité & Limites (4/7) ⚠️
- ✅ UC-053: Tick risque - Vérifier limites
- ✅ UC-054: Limites globales
- ✅ UC-055: Limites par bot
- ✅ UC-056: Arrêt automatique si limite atteinte
- ❌ UC-057: Kill switch manuel global
- ❌ UC-058: Kill switch par bot
- ❌ UC-059: Désactiver kill switch

### 🟣 11. Historique & Transparence (3/5) ⚠️
- ✅ UC-060: Voir historique décisions bot
- ✅ UC-061: Voir historique trades
- ❌ UC-062: Comprendre pourquoi décision prise
- ❌ UC-063: Comprendre pourquoi position fermée
- ✅ UC-064: Export audit trail

---

## ❌ Use Cases restants (14/64)

### Priorité HAUTE (à faire en premier)

1. **UC-040**: Ordre annulé ⭐️⭐️
   - Gérer statut CANCELLED
   - Event: `OrderCancelled`

5. **UC-041**: Ordre échoué ⭐️⭐️
   - Gérer statut FAILED
   - Event: `OrderFailed`
   - Notifier user

### Priorité MOYENNE

3. **UC-044**: Vérifier thèse toujours valide ⭐️
   - Logic dans Strategy domain
   - Comparer market data vs thesis

7. **UC-045**: Détecter news impactant position ⭐️
   - Corréler news avec positions ouvertes
   - Scoring d'impact
2
8. **UC-048**: Bot propose de sortir ⭐️
   - Command: `ProposeExit`
   - Logic: conditions de sortie

9. **UC-049**: Bot propose de réduire ⭐️
   - Command: `ProposeReduce`
   - Logic: reduce position size

10. **UC-050**: Bot propose de ne rien faire ⭐️
    - Logging "no action"

11. **UC-051**: Valider action de sortie ⭐️
    - Command: `ApproveExit`
    - Similar to AcceptProposal

12. **UC-052**: Exécuter sortie automatique ⭐️⭐️
    - Emergency exit (no approval)
    - Event: `EmergencyExitExecuted`

### Priorité BASSE (polish)

13. **UC-057**: Kill switch manuel global ⭐️
    - Command: `ActivateGlobalKillSwitch`
    - Stop all bots

14. **UC-058**: Kill switch par bot ⭐️
    - Command: `ActivateBotKillSwitch`
    - Stop specific bot

15. **UC-059**: Désactiver kill switch ⭐️
    - Command: `DeactivateKillSwitch`

16. **UC-062**: Comprendre pourquoi décision prise ⭐️
    - Query enrichie avec reasoning
    - Format human-readable

17. **UC-063**: Comprendre pourquoi position fermée ⭐️
    - Query enrichie avec closure reason
    - Timeline des événements

---

## 🎯 Prochaine étape: UC-040/041 (Ordres annulés/échoués)

### Fichiers à modifier

**Trading Context**:

1. **Domain Layer**:
   - Ajouter status `CANCELLED` et `FAILED` dans `OrderStatus` enum
   - Méthodes `cancel()` et `markAsFailed()` dans `Order` aggregate
   - Events: `OrderCancelled`, `OrderFailed`

2. **Application Layer**:
   - `Trading/Application/Command/CancelOrder.php`
   - `Trading/Application/Handler/CancelOrderHandler.php`
   - Handler pour gérer ordres failed (webhook Binance)

3. **UI Layer**:
   - Endpoint: `POST /api/orders/{id}/cancel`
   - Webhook: `POST /api/orders/webhook` (Binance callbacks)

---

## 📈 Statistiques techniques

- **Controllers**: 13 (ajout ProposalController)
- **Domain Models**: 15 aggregates (ajout Proposal)
- **Commands**: ~34
- **Handlers**: ~34
- **Events**: ~44
- **Repositories**: ~15
- **Tests**: 32 fichiers

---

## 🚀 Roadmap Sprint final

### Sprint actuel: Propositions & Validation
- UC-031, UC-032, UC-033 (accepter/refuser/expirer)
- UC-040, UC-041 (ordres annulés/échoués)

### Sprint suivant: Actions de suivi
- UC-044, UC-045 (vérifier thèse, news impactantes)
- UC-048, UC-049, UC-050 (propositions sortie/réduction)
- UC-051, UC-052 (valider/exécuter sorties)

### Sprint final: Kill switches & Explainability
- UC-057, UC-058, UC-059 (kill switches)
- UC-062, UC-063 (explainability)

**Objectif**: 64/64 UC complétés d'ici fin Sprint 🎯
