# Architecture Decision Records

Les ADR documentent les décisions architecturales importantes du projet.

## Format ADR

Chaque ADR doit suivre ce format :

```markdown
# ADR-XXX: Titre de la décision

Date: YYYY-MM-DD
Statut: [Proposé | Accepté | Déprécié | Remplacé par ADR-YYY]

## Contexte

Quel est le problème ou la question à résoudre ?

## Décision

Quelle est la décision prise ?

## Conséquences

Quelles sont les conséquences (positives et négatives) de cette décision ?

## Alternatives considérées

Quelles autres options ont été envisagées et pourquoi ont-elles été rejetées ?
```

---

## Liste des ADR

- [ADR-001: Architecture DDD/Hexagonale](./adr-001-architecture-ddd.md)
- [ADR-002: Choix de Symfony comme framework](./adr-002-symfony-framework.md)
- [ADR-003: Stockage en décimal pour montants financiers](./adr-003-decimal-storage.md)
- [ADR-004: State machines explicites](./adr-004-state-machines.md)
- [ADR-005: Idempotence des ordres](./adr-005-idempotence.md)
- [ADR-006: FIFO pour calcul P&L](./adr-006-fifo-pnl.md)
- [ADR-007: Modes bots (Conseil/Auto protégé/Auto)](./adr-007-bot-modes.md)
- [ADR-008: Multi-portefeuilles dès V1](./adr-008-multi-portfolio.md)
- [ADR-009: Pas de backtesting en V1](./adr-009-no-backtesting.md)
- [ADR-010: Extensibilité via adapters](./adr-010-exchange-adapters.md)

---

## ADR en cours de discussion

Créez un nouveau fichier dans ce dossier pour chaque nouvelle décision architecturale.
