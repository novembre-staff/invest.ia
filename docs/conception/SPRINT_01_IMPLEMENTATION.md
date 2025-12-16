# Plan d'Implémentation - Sprint 1: Fondations

**Objectif**: Mettre en place l'infrastructure de base et l'authentification utilisateur.

**Use Cases ciblés**: UC-001 à UC-005, UC-015

---

## Checklist d'implémentation

### ✅ Structure projet Symfony

- [x] Architecture DDD créée
- [ ] Installation Symfony 6.4+
- [ ] Configuration Doctrine
- [ ] Configuration Messenger
- [ ] Configuration Security
- [ ] Configuration CORS

### 📦 Dépendances à installer

```bash
cd apps/api

# Core Symfony
composer require symfony/orm-pack
composer require symfony/messenger
composer require symfony/security-bundle
composer require symfony/mailer

# JWT Authentication
composer require lexik/jwt-authentication-bundle

# Validation
composer require symfony/validator

# Serialization
composer require symfony/serializer-pack

# Math précis
composer require brick/math

# HTTP Client (pour Binance)
composer require symfony/http-client

# Dev
composer require --dev symfony/maker-bundle
composer require --dev symfony/test-pack
composer require --dev phpunit/phpunit
```

---

## UC-001: Créer un compte

### Étape 1: Domain Layer

**Fichier**: `apps/api/src/Identity/Domain/Model/User.php`
```php
<?php

namespace App\Identity\Domain\Model;

use App\Identity\Domain\ValueObject\Email;
use App\Identity\Domain\ValueObject\UserId;
use App\Identity\Domain\ValueObject\HashedPassword;

class User
{
    private UserId $id;
    private Email $email;
    private HashedPassword $password;
    private string $firstName;
    private string $lastName;
    private UserStatus $status;
    private bool $mfaEnabled;
    private ?\DateTimeImmutable $emailVerifiedAt;
    private \DateTimeImmutable $createdAt;
    
    public function __construct(
        UserId $id,
        Email $email,
        HashedPassword $password,
        string $firstName,
        string $lastName
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->password = $password;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->status = UserStatus::PENDING_VERIFICATION;
        $this->mfaEnabled = false;
        $this->emailVerifiedAt = null;
        $this->createdAt = new \DateTimeImmutable();
    }
    
    public function verifyEmail(): void
    {
        if ($this->status !== UserStatus::PENDING_VERIFICATION) {
            throw new \DomainException('Email already verified');
        }
        
        $this->status = UserStatus::ACTIVE;
        $this->emailVerifiedAt = new \DateTimeImmutable();
    }
    
    // Getters...
}
```

**Fichier**: `apps/api/src/Identity/Domain/ValueObject/Email.php`
```php
<?php

namespace App\Identity\Domain\ValueObject;

class Email
{
    private string $value;
    
    public function __construct(string $email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email format');
        }
        
        $this->value = strtolower($email);
    }
    
    public function getValue(): string
    {
        return $this->value;
    }
    
    public function __toString(): string
    {
        return $this->value;
    }
}
```

**Fichier**: `apps/api/src/Identity/Domain/ValueObject/UserId.php`
```php
<?php

namespace App\Identity\Domain\ValueObject;

use Symfony\Component\Uid\Uuid;

class UserId
{
    private string $value;
    
    private function __construct(string $uuid)
    {
        $this->value = $uuid;
    }
    
    public static function generate(): self
    {
        return new self(Uuid::v4()->toRfc4122());
    }
    
    public static function fromString(string $uuid): self
    {
        if (!Uuid::isValid($uuid)) {
            throw new \InvalidArgumentException('Invalid UUID');
        }
        
        return new self($uuid);
    }
    
    public function getValue(): string
    {
        return $this->value;
    }
    
    public function __toString(): string
    {
        return $this->value;
    }
}
```

**Fichier**: `apps/api/src/Identity/Domain/ValueObject/HashedPassword.php`
```php
<?php

namespace App\Identity\Domain\ValueObject;

class HashedPassword
{
    private string $hash;
    
    private function __construct(string $hash)
    {
        $this->hash = $hash;
    }
    
    public static function fromPlainPassword(string $plainPassword): self
    {
        if (strlen($plainPassword) < 8) {
            throw new \InvalidArgumentException('Password must be at least 8 characters');
        }
        
        // Will be hashed by Symfony's PasswordHasher
        return new self($plainPassword);
    }
    
    public static function fromHash(string $hash): self
    {
        return new self($hash);
    }
    
    public function getHash(): string
    {
        return $this->hash;
    }
}
```

**Fichier**: `apps/api/src/Identity/Domain/ValueObject/UserStatus.php`
```php
<?php

namespace App\Identity\Domain\ValueObject;

enum UserStatus: string
{
    case PENDING_VERIFICATION = 'pending_verification';
    case ACTIVE = 'active';
    case SUSPENDED = 'suspended';
    case DELETED = 'deleted';
}
```

**Fichier**: `apps/api/src/Identity/Domain/Event/UserRegistered.php`
```php
<?php

namespace App\Identity\Domain\Event;

use App\Identity\Domain\ValueObject\UserId;
use App\Identity\Domain\ValueObject\Email;

class UserRegistered
{
    public function __construct(
        public readonly UserId $userId,
        public readonly Email $email,
        public readonly \DateTimeImmutable $occurredAt
    ) {}
}
```

**Fichier**: `apps/api/src/Identity/Domain/Repository/UserRepositoryInterface.php`
```php
<?php

namespace App\Identity\Domain\Repository;

use App\Identity\Domain\Model\User;
use App\Identity\Domain\ValueObject\UserId;
use App\Identity\Domain\ValueObject\Email;

interface UserRepositoryInterface
{
    public function save(User $user): void;
    public function findById(UserId $id): ?User;
    public function findByEmail(Email $email): ?User;
    public function emailExists(Email $email): bool;
}
```

### Étape 2: Application Layer

**Fichier**: `apps/api/src/Identity/Application/Command/RegisterUser.php`
```php
<?php

namespace App\Identity\Application\Command;

class RegisterUser
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
        public readonly string $firstName,
        public readonly string $lastName
    ) {}
}
```

**Fichier**: `apps/api/src/Identity/Application/Handler/RegisterUserHandler.php`
```php
<?php

namespace App\Identity\Application\Handler;

use App\Identity\Application\Command\RegisterUser;
use App\Identity\Domain\Model\User;
use App\Identity\Domain\ValueObject\UserId;
use App\Identity\Domain\ValueObject\Email;
use App\Identity\Domain\ValueObject\HashedPassword;
use App\Identity\Domain\Repository\UserRepositoryInterface;
use App\Identity\Domain\Event\UserRegistered;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsMessageHandler]
class RegisterUserHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private MessageBusInterface $eventBus
    ) {}
    
    public function __invoke(RegisterUser $command): UserId
    {
        $email = new Email($command->email);
        
        // Check if email already exists
        if ($this->userRepository->emailExists($email)) {
            throw new \DomainException('Email already registered');
        }
        
        $userId = UserId::generate();
        $hashedPassword = HashedPassword::fromPlainPassword($command->password);
        
        $user = new User(
            $userId,
            $email,
            $hashedPassword,
            $command->firstName,
            $command->lastName
        );
        
        $this->userRepository->save($user);
        
        // Dispatch event
        $this->eventBus->dispatch(
            new UserRegistered($userId, $email, new \DateTimeImmutable())
        );
        
        return $userId;
    }
}
```

### Étape 3: Infrastructure Layer

**Fichier**: `apps/api/src/Identity/Infrastructure/Persistence/Doctrine/UserDoctrineRepository.php`
```php
<?php

namespace App\Identity\Infrastructure\Persistence\Doctrine;

use App\Identity\Domain\Model\User;
use App\Identity\Domain\ValueObject\UserId;
use App\Identity\Domain\ValueObject\Email;
use App\Identity\Domain\Repository\UserRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserDoctrineRepository extends ServiceEntityRepository implements UserRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }
    
    public function save(User $user): void
    {
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }
    
    public function findById(UserId $id): ?User
    {
        return $this->find($id->getValue());
    }
    
    public function findByEmail(Email $email): ?User
    {
        return $this->findOneBy(['email.value' => $email->getValue()]);
    }
    
    public function emailExists(Email $email): bool
    {
        return $this->count(['email.value' => $email->getValue()]) > 0;
    }
}
```

**Fichier**: `apps/api/src/Identity/Infrastructure/Persistence/Doctrine/User.orm.xml`
```xml
<?xml version="1.0" encoding="UTF-8"?>
<doctrine-mapping>
    <entity name="App\Identity\Domain\Model\User" table="users">
        <id name="id" type="string" column="id">
            <generator strategy="NONE"/>
        </id>
        
        <embedded name="email" class="App\Identity\Domain\ValueObject\Email"/>
        <embedded name="password" class="App\Identity\Domain\ValueObject\HashedPassword"/>
        
        <field name="firstName" column="first_name" type="string"/>
        <field name="lastName" column="last_name" type="string"/>
        <field name="status" type="string" enumType="App\Identity\Domain\ValueObject\UserStatus"/>
        <field name="mfaEnabled" column="mfa_enabled" type="boolean"/>
        <field name="emailVerifiedAt" column="email_verified_at" type="datetime_immutable" nullable="true"/>
        <field name="createdAt" column="created_at" type="datetime_immutable"/>
    </entity>
</doctrine-mapping>
```

### Étape 4: UI Layer (Controller)

**Fichier**: `apps/api/src/Identity/UI/Http/Controller/AuthController.php`
```php
<?php

namespace App\Identity\UI\Http\Controller;

use App\Identity\Application\Command\RegisterUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/auth')]
class AuthController extends AbstractController
{
    public function __construct(
        private MessageBusInterface $commandBus,
        private ValidatorInterface $validator
    ) {}
    
    #[Route('/register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        // Validation basique
        if (!isset($data['email'], $data['password'], $data['firstName'], $data['lastName'])) {
            return new JsonResponse(
                ['error' => 'Missing required fields'],
                Response::HTTP_BAD_REQUEST
            );
        }
        
        try {
            $command = new RegisterUser(
                $data['email'],
                $data['password'],
                $data['firstName'],
                $data['lastName']
            );
            
            $envelope = $this->commandBus->dispatch($command);
            $handledStamp = $envelope->last(HandledStamp::class);
            $userId = $handledStamp->getResult();
            
            return new JsonResponse([
                'userId' => (string) $userId,
                'email' => $data['email'],
                'status' => 'pending_verification'
            ], Response::HTTP_CREATED);
            
        } catch (\DomainException $e) {
            return new JsonResponse(
                ['error' => $e->getMessage()],
                Response::HTTP_CONFLICT
            );
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(
                ['error' => $e->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        }
    }
}
```

---

## Migration base de données

**Fichier**: `apps/api/migrations/Version20251216_CreateUsers.php`
```php
<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251216_CreateUsers extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create users table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE users (
                id UUID PRIMARY KEY,
                email VARCHAR(255) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                first_name VARCHAR(100) NOT NULL,
                last_name VARCHAR(100) NOT NULL,
                status VARCHAR(50) NOT NULL,
                mfa_enabled BOOLEAN DEFAULT FALSE,
                email_verified_at TIMESTAMP,
                created_at TIMESTAMP NOT NULL,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_email (email),
                INDEX idx_status (status)
            )
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE users');
    }
}
```

---

## Tests

**Fichier**: `apps/api/tests/Identity/Application/Handler/RegisterUserHandlerTest.php`
```php
<?php

namespace App\Tests\Identity\Application\Handler;

use App\Identity\Application\Command\RegisterUser;
use App\Identity\Application\Handler\RegisterUserHandler;
use PHPUnit\Framework\TestCase;

class RegisterUserHandlerTest extends TestCase
{
    public function testRegisterUserSuccessfully(): void
    {
        // TODO: Implement test with mocks
        $this->markTestIncomplete();
    }
    
    public function testRegisterUserWithExistingEmail(): void
    {
        $this->expectException(\DomainException::class);
        // TODO: Implement test
        $this->markTestIncomplete();
    }
    
    public function testRegisterUserWithInvalidEmail(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        // TODO: Implement test
        $this->markTestIncomplete();
    }
}
```

---

## Configuration Symfony

**Fichier**: `apps/api/config/packages/messenger.yaml`
```yaml
framework:
    messenger:
        failure_transport: failed

        transports:
            async:
                dsn: '%env(MESSENGER_TRANSPORT_DSN)%'
                retry_strategy:
                    max_retries: 3
                    delay: 1000
                    multiplier: 2
            
            failed: 'doctrine://default?queue_name=failed'

        routing:
            'App\Identity\Domain\Event\*': async
            'App\*\Domain\Event\*': async
```

---

## Prochaines étapes

1. ✅ Implémenter UC-001 (structure ci-dessus)
2. Implémenter UC-002 (Login)
3. Implémenter UC-003 (Logout)
4. Implémenter UC-004 (MFA)
5. Implémenter UC-005 (Préférences)

**Ordre des fichiers à créer pour UC-001**:
1. ValueObjects (Email, UserId, HashedPassword, UserStatus)
2. Model (User)
3. Event (UserRegistered)
4. Repository Interface
5. Command
6. Handler
7. Repository Implementation (Doctrine)
8. Doctrine Mapping
9. Migration
10. Controller
11. Tests

---

## Checklist validation UC-001

- [ ] ValueObjects créés et testés
- [ ] Aggregate User créé
- [ ] Repository interface définie
- [ ] Command et Handler implémentés
- [ ] Persistence Doctrine configurée
- [ ] Migration exécutée
- [ ] Controller créé
- [ ] Tests unitaires passent
- [ ] Test d'intégration POST /api/auth/register fonctionne
- [ ] Événement UserRegistered émis
- [ ] Documentation API mise à jour
