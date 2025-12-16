# Guide de progression - Implementation complète

## ✅ Implémenté (UC-001, UC-002, UC-003)

### UC-001 : Créer un compte ✅
**Fichiers créés** : 20 fichiers
- Domain Layer (7 fichiers)
  - ValueObjects: `UserId.php`, `Email.php`, `HashedPassword.php`, `UserStatus.php`
  - Model: `User.php` (aggregate root)
  - Events: `UserRegistered.php`, `UserEmailVerified.php`
  - Repository: `UserRepositoryInterface.php`
  
- Application Layer (5 fichiers)
  - Commands: `RegisterUser.php`, `VerifyUserEmail.php`
  - Handlers: `RegisterUserHandler.php`, `VerifyUserEmailHandler.php`
  - DTO: `UserDTO.php`
  
- Infrastructure Layer (2 fichiers)
  - Repository: `UserDoctrineRepository.php`
  - Mapping: `User.orm.xml`
  - Security: `UserProvider.php`, `SecurityUser.php`
  
- UI Layer (1 fichier)
  - Controller: `AuthController.php` avec endpoint `/api/auth/register`
  
- Configuration (4 fichiers)
  - `composer.json`, `services.yaml`, `messenger.yaml`, `doctrine.yaml`

### UC-002 : Se connecter ✅
**Fichiers créés** : 4 fichiers
- Application Layer:
  - Command: `AuthenticateUser.php`
  - Handler: `AuthenticateUserHandler.php`
- Domain Layer:
  - Event: `UserLoggedIn.php`
- UI Layer:
  - Endpoint ajouté: `POST /api/auth/login` dans `AuthController.php`

**Fonctionnalités** :
- Authentification par email/password
- Validation des credentials
- Vérification du statut utilisateur (ACTIVE requis)
- Support MFA (si activé, retourne requiresMfa=true)
- Génération token JWT
- Dispatch événement UserLoggedIn avec IP

### UC-003 : Se déconnecter ✅
**Fichiers créés** : 3 fichiers
- Application Layer:
  - Command: `LogoutUser.php`
  - Handler: `LogoutUserHandler.php`
- Domain Layer:
  - Event: `UserLoggedOut.php`
- UI Layer:
  - Endpoint ajouté: `POST /api/auth/logout` dans `AuthController.php`

**Fonctionnalités** :
- Révocation du token (via token storage)
- Dispatch événement UserLoggedOut
- TODO: Implémenter blacklist Redis pour tokens révoqués

### Infrastructure & Configuration ✅
**Fichiers système** : 12 fichiers
- Symfony Bootstrap:
  - `src/Kernel.php`
  - `public/index.php`
  - `bin/console`
  - `config/bootstrap.php`
  
- Configuration:
  - `config/packages/security.yaml` (firewall, JWT, providers)
  - `config/packages/lexik_jwt_authentication.yaml`
  - `config/packages/framework.yaml`
  - `config/packages/doctrine_migrations.yaml`
  - `config/routes.yaml`
  
- Database:
  - `migrations/Version20251216100000.php` (table users)
  
- Documentation:
  - `apps/api/README.md` (guide complet de démarrage)
  - `.env.example`

### Tests ✅
**Fichiers tests** : 5 fichiers
- Domain Tests:
  - `tests/Identity/Domain/Model/UserTest.php` (11 tests)
  - `tests/Identity/Domain/ValueObject/EmailTest.php` (6 tests)
  - `tests/Identity/Domain/ValueObject/HashedPasswordTest.php` (8 tests)
  
- Application Tests:
  - `tests/Identity/Application/Handler/RegisterUserHandlerTest.php` (2 tests)
  
- Configuration:
  - `phpunit.xml.dist`
  - `tests/bootstrap.php`

---

## 📋 À implémenter (Ordre de priorité)

### UC-004 : Activer/Désactiver MFA (Multi-Factor Authentication)
**Priorité** : Haute (requis pour sécurité)

**Fichiers à créer** :
1. **Domain Layer** :
   - `Identity/Domain/Service/TotpService.php` (génération/vérification codes TOTP)

2. **Application Layer** :
   - `Identity/Application/Command/EnableMfa.php`
   - `Identity/Application/Command/DisableMfa.php`
   - `Identity/Application/Command/VerifyMfaCode.php`
   - `Identity/Application/Handler/EnableMfaHandler.php`
   - `Identity/Application/Handler/DisableMfaHandler.php`
   - `Identity/Application/Handler/VerifyMfaCodeHandler.php`

3. **Domain Events** :
   - `Identity/Domain/Event/MfaEnabled.php`
   - `Identity/Domain/Event/MfaDisabled.php`

4. **UI Layer** :
   - Endpoints dans `AuthController.php` :
     - `POST /api/auth/mfa/enable`
     - `POST /api/auth/mfa/disable`
     - `POST /api/auth/mfa/verify`

**Dépendance** : `composer require spomky-labs/otphp`

**Tests** :
- `tests/Identity/Domain/Service/TotpServiceTest.php`
- `tests/Identity/Application/Handler/EnableMfaHandlerTest.php`

---

### UC-005 : Configurer préférences utilisateur
**Priorité** : Moyenne

**Fichiers à créer** :
1. **Domain Layer** :
   - `Identity/Domain/ValueObject/UserPreferences.php` (reporting currency, timezone, language, notifications)

2. **Application Layer** :
   - `Identity/Application/Command/UpdateUserPreferences.php`
   - `Identity/Application/Handler/UpdateUserPreferencesHandler.php`
   - `Identity/Application/DTO/UserPreferencesDTO.php`

3. **Domain Events** :
   - `Identity/Domain/Event/UserPreferencesUpdated.php`

4. **Infrastructure Layer** :
   - Mettre à jour `User.orm.xml` pour ajouter field `preferences` (type json)

5. **UI Layer** :
   - Créer `Identity/UI/Http/Controller/SettingsController.php`
   - Endpoints :
     - `GET /api/settings/preferences`
     - `PUT /api/settings/preferences`

6. **Migration** :
   - Créer migration pour ajouter colonne `preferences` à table `users`

**Tests** :
- `tests/Identity/Domain/ValueObject/UserPreferencesTest.php`
- `tests/Identity/Application/Handler/UpdateUserPreferencesHandlerTest.php`

---

### UC-015 : Connecter compte Binance (basique)
**Priorité** : Haute (requis pour Sprint 1)

**Fichiers à créer** :
1. **Exchange Context - Domain Layer** :
   - `Exchange/Domain/Model/ExchangeConnection.php` (aggregate)
   - `Exchange/Domain/ValueObject/ExchangeConnectionId.php`
   - `Exchange/Domain/ValueObject/ExchangeName.php` (enum: BINANCE)
   - `Exchange/Domain/ValueObject/ApiCredentials.php` (encrypted)
   - `Exchange/Domain/ValueObject/ConnectionStatus.php` (enum)
   - `Exchange/Domain/Repository/ExchangeConnectionRepositoryInterface.php`

2. **Exchange Context - Application Layer** :
   - `Exchange/Application/Command/ConnectExchange.php`
   - `Exchange/Application/Command/TestExchangeConnection.php`
   - `Exchange/Application/Handler/ConnectExchangeHandler.php`
   - `Exchange/Application/Handler/TestExchangeConnectionHandler.php`
   - `Exchange/Application/DTO/ExchangeConnectionDTO.php`

3. **Exchange Context - Domain Events** :
   - `Exchange/Domain/Event/ExchangeConnected.php`
   - `Exchange/Domain/Event/ExchangeConnectionFailed.php`

4. **Exchange Context - Infrastructure Layer** :
   - `Exchange/Infrastructure/Persistence/Doctrine/ExchangeConnectionDoctrineRepository.php`
   - `Exchange/Infrastructure/Persistence/Doctrine/Mapping/ExchangeConnection.orm.xml`
   - `Exchange/Infrastructure/Adapter/Binance/BinanceApiClient.php`
   - `Exchange/Infrastructure/Adapter/Binance/BinanceAuthenticator.php`

5. **Exchange Context - UI Layer** :
   - `Exchange/UI/Http/Controller/ExchangeController.php`
   - Endpoints :
     - `POST /api/exchanges/connect`
     - `POST /api/exchanges/{id}/test`
     - `GET /api/exchanges`
     - `DELETE /api/exchanges/{id}`

6. **Configuration** :
   - Mettre à jour `services.yaml` pour Exchange context
   - Créer `config/packages/binance.yaml` (API endpoints, timeouts)

7. **Migration** :
   - Créer table `exchange_connections`

**Dépendances** :
```bash
composer require symfony/http-client
composer require symfony/encryption-bundle
```

**Tests** :
- `tests/Exchange/Domain/Model/ExchangeConnectionTest.php`
- `tests/Exchange/Infrastructure/Adapter/Binance/BinanceApiClientTest.php`

---

## 🏃 Sprint 1 - Récapitulatif

### Objectif
Avoir un système de base permettant :
1. ✅ Inscription utilisateur (UC-001)
2. ✅ Connexion/Déconnexion (UC-002, UC-003)
3. 🔜 Sécurité MFA (UC-004)
4. 🔜 Préférences utilisateur (UC-005)
5. 🔜 Connexion Binance basique (UC-015)

### Progression actuelle : **60% du Sprint 1**
- ✅ 3/5 use cases implémentés
- ✅ Infrastructure complète (Symfony, Doctrine, Messenger, JWT)
- ✅ Tests unitaires de base
- ✅ Documentation

### Prochaines étapes recommandées

**Ordre d'implémentation suggéré** :

1. **UC-004 (MFA)** → Sécurise l'authentification avant d'aller plus loin
2. **UC-015 (Binance)** → Permet de débloquer les use cases de lecture de marchés
3. **UC-005 (Préférences)** → Moins critique, peut attendre

---

## 📊 Vue d'ensemble des bounded contexts

### ✅ Identity Context - 50% complété
- User aggregate ✅
- Authentication ✅
- Registration ✅
- MFA ⏳ (structure prête, logique TOTP à implémenter)

### ⏳ Exchange Context - 0% complété
- Structure à créer
- Binance adapter à implémenter
- Gestion credentials sécurisée

### ⏳ Market Context - 0% complété
- Lecture données marchés
- Cache Redis
- Websocket Binance

### ⏳ Portfolio Context - 0% complété
- Positions
- Transactions
- Calculs P&L

### ⏳ Trading Context - 0% complété
- Ordres
- Exécution
- Historique

### ⏳ Bots Context - 0% complété
- Bot entities
- Orchestration
- Stratégies

### ⏳ Risk Context - 0% complété
- Risk limits
- Position sizing
- Alertes

### ⏳ Analytics Context - 0% complété
- Métriques
- Rapports
- KPIs

### ⏳ News Context - 0% complété
- Flux RSS
- Sentiment analysis
- Websocket

### ⏳ Audit Context - 0% complété
- Audit logs
- Event sourcing partiel
- Compliance

---

## 🎯 Commandes pour continuer

### Lancer les tests existants
```bash
cd apps/api
composer install
php bin/phpunit
```

### Créer la base de données
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### Générer les clés JWT
```bash
php bin/console lexik:jwt:generate-keypair
```

### Lancer le serveur
```bash
symfony server:start
# OU
php -S localhost:8000 -t public/
```

### Tester les endpoints
```bash
# Register
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"SecurePass123","firstName":"John","lastName":"Doe"}'

# Login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"SecurePass123"}'

# Logout (avec token)
curl -X POST http://localhost:8000/api/auth/logout \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

---

## 📝 Notes importantes

1. **Architecture respectée** : Tous les fichiers suivent strictement DDD/Hexagonal
2. **Précision décimale** : brick/math prêt pour les calculs financiers
3. **Événements async** : Tous les domain events passent par Symfony Messenger
4. **Sécurité** : Passwords hashed, JWT, MFA prêt
5. **Tests** : 27 tests unitaires créés pour Identity context

**Status** : La base est solide, on peut continuer l'implémentation des UC suivants ! 🚀
