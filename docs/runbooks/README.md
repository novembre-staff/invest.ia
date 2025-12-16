# Runbooks - Procédures d'incidents et opérations

Les runbooks documentent les procédures à suivre en cas d'incident ou pour des opérations courantes.

---

## Liste des runbooks

### Incidents

- [RB-001: Exchange déconnecté](./rb-001-exchange-disconnected.md)
- [RB-002: Bot en erreur critique](./rb-002-bot-critical-error.md)
- [RB-003: Discrepancy portfolio](./rb-003-portfolio-discrepancy.md)
- [RB-004: Rate limit atteint](./rb-004-rate-limit-reached.md)
- [RB-005: Ordre bloqué](./rb-005-order-stuck.md)

### Opérations

- [RB-010: Déploiement production](./rb-010-production-deployment.md)
- [RB-011: Rotation clés API](./rb-011-api-key-rotation.md)
- [RB-012: Réconciliation manuelle](./rb-012-manual-reconciliation.md)
- [RB-013: Export audit trail](./rb-013-audit-export.md)
- [RB-014: Activation kill switch](./rb-014-kill-switch-activation.md)

### Monitoring

- [RB-020: Alertes à surveiller](./rb-020-monitoring-alerts.md)
- [RB-021: Métriques clés](./rb-021-key-metrics.md)

---

## Template runbook

Chaque runbook doit suivre ce format :

```markdown
# RB-XXX: Titre du runbook

## Symptômes

Comment identifier le problème ?

## Impact

Quel est l'impact sur les utilisateurs / le système ?

## Diagnostic

Comment confirmer le problème ?
- Commandes à exécuter
- Logs à vérifier
- Métriques à consulter

## Résolution

### Immédiate (mitigation)

Actions rapides pour limiter l'impact.

### Définitive

Actions pour résoudre complètement le problème.

## Vérification

Comment vérifier que le problème est résolu ?

## Post-mortem

- Documenter l'incident
- Identifier cause racine
- Actions préventives
- Mettre à jour ce runbook si nécessaire

## Contacts

Qui contacter en cas de besoin ?
```

---

## Procédures d'urgence

### Kill Switch Global

**Quand** : Anomalie critique détectée, comportement anormal des bots.

**Comment** :
1. Se connecter au dashboard admin
2. Aller dans Risk Center
3. Activer "Kill Switch Global"
4. Tous les bots passent immédiatement en HALT
5. Aucun nouvel ordre ne peut être soumis
6. Investiguer avant réactivation

**CLI** :
```bash
php bin/console risk:kill-switch:activate --global --reason="Description"
```

### Pause d'urgence d'un bot

**Quand** : Comportement anormal d'un bot spécifique.

**Comment** :
```bash
php bin/console bot:pause <bot-id> --reason="Description"
```

### Vérification santé système

```bash
# Status exchanges
php bin/console exchange:health:check

# Status bots
php bin/console bot:status --all

# Dernières erreurs
php bin/console audit:errors --last=1h

# Métriques risque
php bin/console risk:exposure:report
```

---

## Escalade

1. **Niveau 1** : Équipe dev/ops (runbooks)
2. **Niveau 2** : Lead tech + product owner
3. **Niveau 3** : CTO + décision business

**Critères escalade niveau 2** :
- Perte financière > seuil défini
- Indisponibilité > 15 minutes
- Données corrompues

**Critères escalade niveau 3** :
- Perte financière critique
- Incident de sécurité
- Problème réglementaire

---

## Checklist post-incident

- [ ] Incident résolu et vérifié
- [ ] Post-mortem rédigé
- [ ] Cause racine identifiée
- [ ] Actions préventives définies
- [ ] Runbook mis à jour
- [ ] Communication aux utilisateurs (si nécessaire)
- [ ] Metrics/monitoring améliorés
- [ ] Tests ajoutés pour éviter récurrence
