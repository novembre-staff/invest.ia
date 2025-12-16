# ADR-003: Stockage en décimal pour montants financiers

Date: 2025-12-16
Statut: Accepté

## Contexte

La plateforme manipule des montants financiers critiques :
- Prix d'actifs (crypto, actions)
- Quantités d'ordres
- Fees
- P&L
- Réserves des bots

L'utilisation de nombres flottants (float/double) est **dangereuse** en finance :
- Erreurs d'arrondi imprévisibles
- Accumulation d'erreurs sur calculs répétés
- Comparaisons d'égalité impossibles
- Risque de litiges utilisateurs
- Non-conformité réglementaire

## Décision

**Tous les montants financiers et quantités sont stockés et manipulés en décimal (type `decimal` ou équivalent selon le langage).**

### Implémentation

**PHP/Symfony** :
- Utiliser `string` en base de données (DECIMAL)
- Bibliothèque `brick/math` pour calculs précis
- Doctrine : mapping `decimal` avec précision adaptée

**Base de données** :
```sql
-- Exemples de précisions
price DECIMAL(20, 8)      -- 8 décimales pour crypto
quantity DECIMAL(20, 8)   -- 8 décimales pour quantités
amount DECIMAL(20, 2)     -- 2 décimales pour fiat
fee DECIMAL(20, 8)        -- 8 décimales pour fees
```

**Règles de précision** :
- Crypto : 8 décimales (standard Binance)
- Fiat : 2 décimales (EUR, USD)
- Pourcentages : 4 décimales
- Ratios : 6 décimales

### Arrondis

- **Quantités** : arrondi DOWN selon stepSize (ne jamais dépasser budget)
- **Prix BUY limit** : arrondi DOWN (plus agressif)
- **Prix SELL limit** : arrondi UP (éviter vendre trop bas)
- **Fees** : pas d'arrondi (valeur exacte)
- **Affichage** : arrondi pour UI mais stockage exact

### Symbol Rules

Chaque symbole a ses propres règles de précision (récupérées de l'exchange) :
- `quantityPrecision`
- `pricePrecision`
- `stepSize`
- `tickSize`
- `minQty`
- `minNotional`

Ces règles sont **stockées en base** et utilisées pour normaliser avant envoi à l'exchange.

## Conséquences

### Positives

- **Exactitude** : calculs financiers précis
- **Conformité** : respect des standards financiers
- **Confiance** : pas d'erreurs d'arrondi surprenantes
- **Audit** : traçabilité exacte
- **Légal** : protection contre litiges

### Négatives

- **Performance** : calculs décimaux plus lents que float (négligeable en pratique)
- **Complexité** : nécessite bibliothèque dédiée
- **Stockage** : types string/decimal prennent plus d'espace

### Mitigations

- Utiliser bibliothèques optimisées (`brick/math`)
- Indexation appropriée en base
- Cache pour calculs répétitifs

## Alternatives considérées

### 1. Float/Double

**Rejeté car** :
```php
// Exemple du problème
0.1 + 0.2 === 0.3  // false en float !
// Inacceptable en finance
```

### 2. Integers (montants en centimes)

**Rejeté car** :
- Inadapté aux crypto (8 décimales)
- Conversions complexes
- Risque d'overflow sur gros montants
- Moins lisible

### 3. Bibliothèques monétaires spécialisées (Money pattern)

**Partiellement adopté** :
- Bon pour fiat
- Insuffisant pour crypto (précision variable)
- Utilisable en complément de decimal

## Exemples d'implémentation

### Entity Doctrine

```php
#[Entity]
class Order
{
    #[Column(type: 'decimal', precision: 20, scale: 8)]
    private string $quantity;
    
    #[Column(type: 'decimal', precision: 20, scale: 8)]
    private string $price;
    
    public function getQuantity(): BigDecimal
    {
        return BigDecimal::of($this->quantity);
    }
}
```

### Service calcul

```php
use Brick\Math\BigDecimal;

class PositionCalculator
{
    public function calculateValue(
        BigDecimal $quantity,
        BigDecimal $price
    ): BigDecimal {
        return $quantity->multipliedBy($price);
    }
}
```

## Validation

- Tests unitaires avec cas limites
- Comparaison avec calculs Excel/référence
- Audits réguliers des montants
- Alertes sur discrepancies

## Références

- IEEE 754 (problèmes float)
- "Decimal Arithmetic" - Mike Cowlishaw
- Documentation Brick/Math
- Binance API precision rules
