# ADR-001: Architecture DDD/Hexagonale

Date: 2025-12-16
Statut: Accepté

## Contexte

La plateforme invest.ia est une application complexe qui doit :
- Gérer plusieurs domaines métier distincts (trading, bots, risques, news, etc.)
- Maintenir une séparation claire des responsabilités
- Faciliter l'évolution et la maintenance
- Permettre l'extensibilité (nouveaux exchanges, providers)
- Garantir la testabilité

## Décision

Nous adoptons une architecture **Domain-Driven Design (DDD)** avec une approche **hexagonale** :

### Bounded Contexts

L'application est découpée en 10 bounded contexts :
- Shared (transverse)
- Identity (auth, users)
- Market (assets, prices)
- News (actualités)
- Exchange (connexions exchanges)
- Portfolio (comptes, positions)
- Trading (ordres, fills)
- Bots (agents décisionnels)
- Risk (limites, kill switch)
- Analytics (KPIs, reporting)
- Audit (traçabilité)

### Structure par contexte

Chaque contexte suit la structure hexagonale :

```
<Context>/
├─ Domain/                  # Cœur métier
│  ├─ Model/               # Agrégats, entités
│  ├─ ValueObject/         # Objects-valeur
│  ├─ Repository/          # Interfaces repositories
│  ├─ Service/             # Services domaine
│  └─ Event/               # Événements métier
├─ Application/            # Cas d'usage
│  ├─ Command/            # Commands CQRS
│  ├─ Query/              # Queries CQRS
│  ├─ Handler/            # Command/Query handlers
│  ├─ DTO/                # Data Transfer Objects
│  └─ Service/            # Services applicatifs
├─ Infrastructure/         # Adaptateurs techniques
│  ├─ Persistence/        # Implémentations repositories
│  ├─ Adapter/            # Adaptateurs externes
│  ├─ Messaging/          # Event bus, queues
│  └─ Mapper/             # Mappeurs domain <-> persistence
└─ UI/                    # Interfaces utilisateur
   ├─ Http/              # Controllers, requests, responses
   └─ Console/           # Commandes CLI
```

## Conséquences

### Positives

- **Séparation des préoccupations** : le domaine métier est isolé de l'infrastructure
- **Testabilité** : le domaine peut être testé sans dépendances externes
- **Évolutivité** : ajout de nouveaux contextes sans impact sur l'existant
- **Maintenabilité** : chaque contexte est autonome et compréhensible
- **Extensibilité** : nouveaux adapters (exchanges, providers) sans modifier le domaine
- **Ubiquitous language** : le code reflète le langage métier

### Négatives

- **Complexité initiale** : plus de structure à mettre en place
- **Courbe d'apprentissage** : l'équipe doit comprendre DDD
- **Risque de sur-ingénierie** : tentation de tout modéliser en DDD
- **Duplication apparente** : DTOs vs entités vs modèles persistence

### Mitigations

- Documentation claire et exemples
- Formation équipe aux principes DDD
- Pragmatisme : ne pas appliquer DDD partout (contexte Shared peut être plus simple)
- Code reviews pour maintenir la cohérence

## Alternatives considérées

### 1. Architecture en couches classique (layered)

**Rejeté car** :
- Couplage fort entre couches
- Difficile d'isoler le métier
- Moins extensible

### 2. Microservices

**Rejeté pour V1 car** :
- Complexité opérationnelle élevée
- Overhead réseau
- Transactions distribuées complexes
- Peut être envisagé en V2+ si scaling nécessaire

### 3. Modular monolith sans DDD

**Rejeté car** :
- Moins de structure formelle
- Risque de dégradation architecturale
- Moins adapté à la complexité métier

## Références

- Evans, Eric. "Domain-Driven Design: Tackling Complexity in the Heart of Software"
- Vernon, Vaughn. "Implementing Domain-Driven Design"
- Cockburn, Alistair. "Hexagonal Architecture"
