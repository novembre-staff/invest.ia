# Guide de démarrage rapide - invest.ia API

## Prérequis

- PHP 8.2+
- Composer 2.x
- PostgreSQL 15+
- Redis 7+
- Make (optionnel)

## Installation

### 1. Installer les dépendances

```bash
cd apps/api
composer install
```

### 2. Configuration de l'environnement

Copier le fichier .env.example à la racine du projet vers apps/api/.env.local et configurer :

```bash
cp ../../.env.example .env.local
```

Éditer `.env.local` :

```env
APP_ENV=dev
APP_SECRET=votre_secret_genere_ici
APP_DEBUG=1

DATABASE_URL="postgresql://investia:investia_secret@localhost:5432/investia?serverVersion=15&charset=utf8"
REDIS_URL=redis://localhost:6379
MESSENGER_TRANSPORT_DSN=redis://localhost:6379/messages
```

### 3. Générer les clés JWT

```bash
php bin/console lexik:jwt:generate-keypair
```

Cela créera :
- `config/jwt/private.pem`
- `config/jwt/public.pem`

### 4. Créer la base de données

```bash
php bin/console doctrine:database:create
```

### 5. Exécuter les migrations

```bash
php bin/console doctrine:migrations:migrate --no-interaction
```

### 6. Vérifier la configuration

```bash
php bin/console about
```

## Lancer l'application

### Option 1: Symfony CLI (recommandé)

```bash
symfony server:start
```

### Option 2: PHP built-in server

```bash
php -S localhost:8000 -t public/
```

### Lancer le worker Messenger

Dans un terminal séparé :

```bash
php bin/console messenger:consume async -vv
```

## Tester l'API

### 1. Créer un compte

```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john.doe@example.com",
    "password": "SecurePass123",
    "firstName": "John",
    "lastName": "Doe"
  }'
```

Réponse attendue (201 Created) :

```json
{
  "userId": "550e8400-e29b-41d4-a716-446655440000",
  "email": "john.doe@example.com",
  "firstName": "John",
  "lastName": "Doe",
  "status": "pending_verification",
  "mfaEnabled": false,
  "emailVerified": false
}
```

### 2. Vérifier l'email (simulé pour le moment)

```bash
curl http://localhost:8000/api/auth/verify-email/550e8400-e29b-41d4-a716-446655440000/token123
```

### 3. Se connecter (à implémenter dans UC-002)

```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john.doe@example.com",
    "password": "SecurePass123"
  }'
```

## Tests

### Lancer tous les tests

```bash
php bin/phpunit
```

### Lancer les tests d'un contexte spécifique

```bash
php bin/phpunit tests/Identity/
```

## Docker (optionnel)

Si vous préférez utiliser Docker :

```bash
cd ../../infra/docker
docker-compose up -d postgres redis
```

Puis dans apps/api, ajuster DATABASE_URL et REDIS_URL dans .env.local :

```env
DATABASE_URL="postgresql://investia:investia_secret@127.0.0.1:5432/investia?serverVersion=15&charset=utf8"
REDIS_URL=redis://127.0.0.1:6379
```

## Commandes utiles

### Doctrine

```bash
# Créer une nouvelle migration
php bin/console make:migration

# Voir le statut des migrations
php bin/console doctrine:migrations:status

# Rollback dernière migration
php bin/console doctrine:migrations:migrate prev

# Vider la base
php bin/console doctrine:schema:drop --force --full-database
```

### Messenger

```bash
# Voir les messages en attente
php bin/console messenger:stats

# Voir les messages échoués
php bin/console messenger:failed:show

# Réessayer les messages échoués
php bin/console messenger:failed:retry
```

### Cache

```bash
# Vider le cache
php bin/console cache:clear

# Warmup du cache
php bin/console cache:warmup
```

### Debug

```bash
# Lister les routes
php bin/console debug:router

# Lister les services
php bin/console debug:container

# Lister les événements
php bin/console debug:event-dispatcher
```

## Prochaines étapes

UC-001 ✅ **Implémenté** : Créer un compte

**À implémenter ensuite** :

- UC-002 : Se connecter (login avec JWT)
- UC-003 : Se déconnecter
- UC-004 : Activer MFA
- UC-005 : Configurer préférences

Voir [SPRINT_01_IMPLEMENTATION.md](../../docs/conception/SPRINT_01_IMPLEMENTATION.md) pour les détails.

## Troubleshooting

### Erreur de connexion PostgreSQL

Vérifier que PostgreSQL est démarré :

```bash
# Windows
net start postgresql-x64-15

# Linux/Mac
sudo systemctl start postgresql
```

### Erreur de connexion Redis

Vérifier que Redis est démarré :

```bash
# Windows
redis-server

# Linux/Mac
sudo systemctl start redis
```

### Erreur de permissions sur les clés JWT

```bash
# Linux/Mac
chmod 600 config/jwt/*.pem
```

### Clear cache si erreur étrange

```bash
php bin/console cache:clear
rm -rf var/cache/*
```

## Support

- Documentation : [docs/](../../docs/)
- Use Cases : [docs/conception/USE_CASES_COMPLETS.md](../../docs/conception/USE_CASES_COMPLETS.md)
- Architecture : [docs/architecture/](../../docs/architecture/)
