# invest.ia

> Dashboard Finance + Crypto avec Bots conseillers et exécutants (argent réel)

Plateforme web de trading automatisé combinant marchés finance classiques et crypto, avec intégration news et bots intelligents.

---

## 🎯 Vision

Une application qui permet de :
- Suivre les marchés finance et crypto en temps réel
- Recevoir et analyser des actualités avec scoring d'impact
- Créer des bots qui **proposent** et **exécutent** des trades sur argent réel
- Contrôler le risque avec des limites strictes et kill switch
- Auditer complètement chaque décision et transaction

**V1 focus** : Binance Spot, mode conseil et auto-protégé, pas de backtesting.

---

## 🏗️ Architecture

### Monorepo structure

```
invest.ia/
├─ apps/
│  ├─ web/          # Frontend (SPA)
│  └─ api/          # Backend Symfony
├─ docs/            # Documentation
├─ infra/           # Infrastructure (Docker, K8s, Terraform)
└─ .github/         # CI/CD
```

### Backend (DDD/Hexagonal)

10 bounded contexts :
- **Shared** : Éléments communs
- **Identity** : Auth, MFA, users
- **Market** : Assets, prix, watchlists
- **News** : Actualités, tagging, scoring
- **Exchange** : Connexions exchanges (Binance)
- **Portfolio** : Comptes, positions, ledger
- **Trading** : Ordres, fills, exécution
- **Bots** : Agents décisionnels
- **Risk** : Limites, kill switch
- **Analytics** : KPIs, reporting
- **Audit** : Traçabilité complète

Voir [Architecture détaillée](./docs/architecture/BOUNDED_CONTEXTS.md)

---

## 📋 Features clés V1

### Markets & Data
- Screener multi-actifs (crypto + finance)
- Watchlists personnalisables
- Prix temps réel avec indicateurs
- Alertes configurables

### News Intelligence
- Agrégation multi-sources
- Tagging automatique des actifs
- Scoring impact et sentiment
- Timeline unifiée prix + news

### Bots
- **3 modes** : Conseil / Auto protégé / Auto
- Réserve budgétaire isolée
- Propositions justifiées
- Validation utilisateur selon mode
- Horizons : court/moyen/long terme

### Risk Management
- Limites globales et par bot
- Kill switch (global + par bot)
- Exposition par actif/secteur
- No-trade windows sur volatilité

### Portfolio & Trading
- Multi-comptes support
- Exécution Binance Spot
- Ledger double-entry
- P&L réalisé (FIFO) et non réalisé
- Réconciliation automatique

### Audit & Compliance
- Traçabilité complète
- Journal de décisions
- Export audit trail
- Support bundles

---

## 🚀 Quick Start

### Prérequis

- PHP 8.2+
- Composer 2.x
- PostgreSQL 15+
- Redis 7+
- Node.js 20+
- Docker & Docker Compose (optionnel)

### Installation

```bash
# Clone le repo
git clone https://github.com/votre-org/invest.ia.git
cd invest.ia

# Backend
cd apps/api
composer install
cp .env .env.local
# Configurer .env.local (DB, Redis, etc.)
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# Frontend
cd ../web
npm install
npm run dev

# Lancer l'app
# Terminal 1 (backend)
cd apps/api
symfony server:start

# Terminal 2 (frontend)
cd apps/web
npm run dev

# Terminal 3 (workers)
cd apps/api
php bin/console messenger:consume async -vv
```

### Docker (alternatif)

```bash
docker-compose up -d
docker-compose exec api composer install
docker-compose exec api php bin/console doctrine:migrations:migrate
```

---

## 📚 Documentation

- [Dossier de conception complet](./docs/conception/DOSSIER_CONCEPTION.md)
- [Bounded Contexts](./docs/architecture/BOUNDED_CONTEXTS.md)
- [Architecture Decision Records](./docs/adr/README.md)
- [Runbooks](./docs/runbooks/README.md)

### Pour les développeurs

- [Guide de contribution](./CONTRIBUTING.md) *(à créer)*
- [Standards de code](./docs/architecture/CODE_STANDARDS.md) *(à créer)*
- [Guide de test](./docs/architecture/TESTING_GUIDE.md) *(à créer)*

---

## 🔐 Sécurité

- **MFA obligatoire** pour trading
- Clés API chiffrées
- Permissions minimales (no withdrawal)
- Rotation régulière
- Audit trail complet
- Rate limiting
- CSRF protection

---

## 🧪 Tests

```bash
# Tests unitaires
cd apps/api
php bin/phpunit

# Tests fonctionnels
php bin/phpunit --group functional

# Tests contexte spécifique
php bin/phpunit tests/Bots/
```

---

## 📊 Monitoring

Métriques clés à surveiller :
- Santé connexions exchange
- Latence ordres
- Taux d'erreur
- P&L bots
- Exposition risque
- Rate limits

---

## 🗺️ Roadmap

### V1.0 (MVP)
- [x] Architecture DDD/Hexa
- [ ] Auth + MFA
- [ ] Connexion Binance
- [ ] Markets + Watchlists
- [ ] News feed
- [ ] Portfolio tracking
- [ ] Bots mode Conseil + Auto protégé
- [ ] Trading exécution
- [ ] Risk Center
- [ ] Audit trail

### V1.1
- [ ] Réconciliation avancée
- [ ] Notifications multi-canaux
- [ ] Analytics dashboard
- [ ] Support autres exchanges

### V2.0
- [ ] Backtesting
- [ ] Paper trading
- [ ] ML/AI enhanced bots
- [ ] Mobile app

---

## 🤝 Contribution

Voir [CONTRIBUTING.md](./CONTRIBUTING.md) *(à créer)*

---

## 📄 Licence

Propriétaire - Tous droits réservés

---

## 👥 Équipe

- Tech Lead : *À définir*
- Product Owner : *À définir*
- Devs : *À définir*

---

## 📞 Support

- Email : support@invest.ia
- Documentation : https://docs.invest.ia
- Status page : https://status.invest.ia

---

**⚠️ Disclaimer** : Ce logiciel manipule de l'argent réel. Utilisez-le à vos risques et périls. Testez abondamment avant utilisation en production.
