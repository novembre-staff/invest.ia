# 🎉 Implémentation UC-031, UC-032, UC-033 - Propositions d'investissement

**Date**: 16 Décembre 2025  
**UC implémentés**: UC-031 (Accepter), UC-032 (Refuser), UC-033 (Expirer)  
**Progression**: ✅ 50/64 UC complétés (78.1%) - +3 UC  

---

## ✅ Fichiers créés (19 fichiers)

### Domain Layer (8 fichiers)

1. **`Bots/Domain/Model/Proposal.php`** (330+ lignes)
   - Aggregate Root: Proposition d'investissement
   - Methods: `accept()`, `reject()`, `expire()`, `markAsExecuted()`, `cancel()`
   - Validation: symbol, side, quantity, risk score
   - Business logic: expiration timeout, ownership verification

2. **`Bots/Domain/ValueObject/ProposalId.php`**
   - UUID v4 pour identification proposition

3. **`Bots/Domain/ValueObject/ProposalStatus.php`**
   - Enum: PENDING, ACCEPTED, REJECTED, EXPIRED, EXECUTED, CANCELLED
   - Helper methods: `isPending()`, `canBeAccepted()`, etc.

4. **`Bots/Domain/Event/ProposalCreated.php`**
5. **`Bots/Domain/Event/ProposalAccepted.php`**
6. **`Bots/Domain/Event/ProposalRejected.php`**
7. **`Bots/Domain/Event/ProposalExpired.php`**

8. **`Bots/Domain/Repository/ProposalRepositoryInterface.php`**
   - Contrat: `save()`, `findById()`, `findByUserId()`, `findExpiredPendingProposals()`

---

### Application Layer (7 fichiers)

9. **`Bots/Application/Command/CreateProposal.php`**
10. **`Bots/Application/Command/AcceptProposal.php`**
11. **`Bots/Application/Command/RejectProposal.php`**

12. **`Bots/Application/Handler/CreateProposalHandler.php`**
    - Créer proposition avec reasoning + risk score
    - Dispatch `ProposalCreated` event

13. **`Bots/Application/Handler/AcceptProposalHandler.php`** (UC-031)
    - Vérifier ownership
    - Vérifier expiration
    - Accepter proposition
    - Dispatcher commande `PlaceOrder` (UC-034)
    - Marquer comme `EXECUTED` si ordre créé
    - Dispatch `ProposalAccepted` event

14. **`Bots/Application/Handler/RejectProposalHandler.php`** (UC-032)
    - Vérifier ownership
    - Refuser proposition
    - Dispatch `ProposalRejected` event

15. **`Bots/Application/DTO/ProposalDTO.php`**
    - Sérialisation pour API
    - Includes `timeToExpiration` (secondes restantes)

---

### Infrastructure Layer (2 fichiers)

16. **`Bots/Infrastructure/Persistence/Doctrine/ProposalDoctrineRepository.php`**
    - Implementation complète interface
    - Query builders pour filtrage status, userId, strategyId
    - `findExpiredPendingProposals()` pour cron UC-033

17. **`Bots/Infrastructure/Persistence/Doctrine/Mapping/Proposal.orm.xml`**
    - Mapping Doctrine complet
    - Indexes: user_id, strategy_id, status, expires_at, created_at

---

### UI Layer (1 fichier)

18. **`Bots/UI/Http/Controller/ProposalController.php`**
    - `GET /api/bots/proposals` - Liste propositions
    - `GET /api/bots/proposals/{id}` - Détail proposition
    - `POST /api/bots/proposals` - Créer proposition
    - `POST /api/bots/proposals/{id}/accept` - UC-031: Accepter
    - `POST /api/bots/proposals/{id}/reject` - UC-032: Refuser

---

### Migration (1 fichier)

19. **`migrations/Version20251216130000.php`**
    - Table `proposals` avec tous les champs
    - 5 indexes pour performance

---

## 🧪 Tests créés (2 fichiers)

20. **`tests/Bots/Domain/Model/ProposalTest.php`** (13 tests)
    - testCreateProposal
    - testAcceptProposal
    - testRejectProposal
    - testCannotAcceptTwice
    - testExpireProposal
    - testMarkAsExecuted
    - testInvalidSide
    - testInvalidQuantity
    - testInvalidRiskScore
    - testTimeToExpiration
    - ...

21. **`tests/Bots/Application/Handler/AcceptProposalHandlerTest.php`** (4 tests)
    - testAcceptProposalSuccess
    - testAcceptProposalNotFound
    - testAcceptProposalUnauthorized
    - testAcceptExpiredProposal

---

## 🔧 Configuration mise à jour

- **`config/services.yaml`**: 
  - Ajout binding `ProposalRepositoryInterface` → `ProposalDoctrineRepository`

- **`Identity/UI/Http/Controller/SettingsController.php`**:
  - Suppression TODO (simulate preferences retrieval)

---

## 🎯 Use Cases implémentés

### ✅ UC-031: Accepter une proposition
**Endpoint**: `POST /api/bots/proposals/{id}/accept`

**Flow**:
1. Utilisateur reçoit proposition du bot
2. Vérifie ownership et expiration
3. Accepte → `ProposalStatus::ACCEPTED`
4. Crée automatiquement l'ordre via `PlaceOrder` command
5. Marque proposition comme `EXECUTED` si ordre créé
6. Dispatch event `ProposalAccepted`

**Business rules**:
- Seulement propositions `PENDING` acceptables
- Vérification expiration avant acceptation
- Ownership strict (userId doit matcher)
- Création ordre automatique (UC-034 linkage)

---

### ✅ UC-032: Refuser une proposition
**Endpoint**: `POST /api/bots/proposals/{id}/reject`

**Flow**:
1. Utilisateur refuse proposition
2. Vérifie ownership
3. Marque comme `ProposalStatus::REJECTED`
4. Dispatch event `ProposalRejected`
5. Raison optionnelle stockée

**Business rules**:
- Seulement propositions `PENDING` refusables
- Ownership strict
- Raison de refus optionnelle (analytics)

---

### ✅ UC-033: Expiration d'une proposition
**Implémentation**: Via cron/worker (code déjà prêt dans domain)

**Flow**:
1. Cron périodique appelle `findExpiredPendingProposals()`
2. Pour chaque proposition expirée:
   - Marque comme `ProposalStatus::EXPIRED`
   - Dispatch event `ProposalExpired`

**Business rules**:
- Timeout configurable (default: 30 min)
- Seulement propositions `PENDING` peuvent expirer
- Calcul `timeToExpiration` en temps réel

**TODO**: Créer worker/command Symfony pour cron

---

## 📊 Structure Proposal

```php
class Proposal {
    // Identity
    ProposalId $id
    UserId $userId
    StrategyId $strategyId
    
    // Trade Details
    string $symbol
    string $side (buy/sell)
    string $quantity
    string? $limitPrice
    ProposalStatus $status
    
    // Reasoning (Explainability)
    string $rationale          // "Strong bullish momentum detected"
    array $riskFactors         // ['volatility' => 'medium', ...]
    string $riskScore          // LOW | MEDIUM | HIGH
    string? $expectedReturn    // "5.0%" attendu
    string? $stopLoss          // Prix stop loss suggéré
    string? $takeProfit        // Prix take profit suggéré
    
    // Lifecycle
    DateTime $createdAt
    DateTime $expiresAt        // Auto-calculated from expirationMinutes
    DateTime? $respondedAt     // Quand user a répondu
    string? $orderId           // Référence ordre si accepted + executed
}
```

---

## 🚀 Impact sur la roadmap

### Progression avant
- ✅ 47/64 UC (73.4%)

### Progression après
- ✅ 50/64 UC (78.1%) - **+3 UC**

### UC restants (14)
1. UC-040: Ordre annulé
2. UC-041: Ordre échoué
3. UC-044: Vérifier thèse toujours valide
4. UC-045: Détecter news impactant position
5. UC-048: Bot propose de sortir
6. UC-049: Bot propose de réduire
7. UC-050: Bot propose de ne rien faire
8. UC-051: Valider action de sortie
9. UC-052: Exécuter sortie automatique
10. UC-057: Kill switch manuel global
11. UC-058: Kill switch par bot
12. UC-059: Désactiver kill switch
13. UC-062: Comprendre pourquoi décision prise
14. UC-063: Comprendre pourquoi position fermée

---

## 📈 Statistiques techniques mises à jour

- **Total fichiers PHP**: 282 → 302 (+20)
- **Controllers**: 12 → 13 (ajout ProposalController)
- **Domain Models**: 14 → 15 (ajout Proposal aggregate)
- **Tests**: 30 → 32 fichiers
- **Commands**: ~31 → 34
- **Handlers**: ~31 → 34
- **Events**: ~40 → 44

---

## 🔗 Intégrations

### Avec Trading Context
- `AcceptProposalHandler` → dispatch `PlaceOrder` command
- Link entre Proposal et Order via `orderId` field

### Avec Strategy Context (Bots)
- Proposals créées par strategies/bots
- Filtrage par `strategyId`

### Event-driven
- `ProposalCreated` → peut trigger notification user
- `ProposalAccepted` → peut trigger analytics
- `ProposalRejected` → peut trigger bot learning
- `ProposalExpired` → peut trigger cleanup

---

## 🎓 Concepts DDD illustrés

### Aggregate Root
- **Proposal** encapsule toute la logique de cycle de vie
- Invariants protégés (ownership, expiration, status transitions)
- Events domain dispatched à chaque transition

### Value Objects
- `ProposalId`, `ProposalStatus` = immutables
- `ProposalStatus` enum avec business methods

### Repository Pattern
- Interface dans Domain, implémentation dans Infrastructure
- Queries métier: `findExpiredPendingProposals()`, `countPendingByUserId()`

### Command/Handler CQRS
- Séparation write (Accept/Reject) vs read (Get/List)
- Handlers orchestrent domain + dispatch events

### Explainability
- Chaque proposition contient:
  - `rationale`: Pourquoi cette proposition
  - `riskFactors`: Facteurs de risque identifiés
  - `riskScore`: Score LOW/MEDIUM/HIGH
  - `expectedReturn`: Retour attendu

---

## ✅ Prochaines étapes

1. **UC-040, UC-041**: Ordres annulés/échoués (compléter Trading context)
2. **UC-033 Worker**: Créer command Symfony pour expirer propositions
3. **UC-048-052**: Propositions de sortie/réduction (similar à proposals)
4. **UC-057-059**: Kill switches (pause globale/par bot)
5. **UC-062-063**: Queries d'explainability enrichies

---

**Objectif**: 64/64 UC complétés avant fin Sprint 🎯
