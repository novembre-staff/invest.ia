# Docker Infrastructure

## Services

- **PostgreSQL** : Base de données principale
- **Redis** : Cache et queues
- **API** : Backend Symfony
- **Web** : Frontend React
- **Nginx** : Reverse proxy

## Quick start

```bash
docker-compose up -d
```

## Services individuels

```bash
# Database only
docker-compose up -d postgres redis

# API
docker-compose up -d api

# Web
docker-compose up -d web
```

## Commandes utiles

```bash
# Logs
docker-compose logs -f api

# Shell dans un container
docker-compose exec api bash

# Symfony console
docker-compose exec api php bin/console cache:clear

# Migrations
docker-compose exec api php bin/console doctrine:migrations:migrate

# Tests
docker-compose exec api php bin/phpunit
```

## Volumes

- `postgres_data` : Données PostgreSQL
- `redis_data` : Données Redis

## Networks

- `invest_ia_network` : Réseau interne

## Ports

- **API** : 8000
- **Web** : 3000
- **PostgreSQL** : 5432
- **Redis** : 6379

## Variables d'environnement

Voir `.env.example` à la racine du projet.
