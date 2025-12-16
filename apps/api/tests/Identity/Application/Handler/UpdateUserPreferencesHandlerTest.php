<?php

declare(strict_types=1);

namespace App\Tests\Identity\Application\Handler;

use App\Identity\Application\Command\UpdateUserPreferences;
use App\Identity\Application\Handler\UpdateUserPreferencesHandler;
use App\Identity\Domain\Event\UserPreferencesUpdated;
use App\Identity\Domain\Model\User;
use App\Identity\Domain\Repository\UserRepositoryInterface;
use App\Identity\Domain\ValueObject\Email;
use App\Identity\Domain\ValueObject\HashedPassword;
use App\Identity\Domain\ValueObject\UserId;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class UpdateUserPreferencesHandlerTest extends TestCase
{
    private UserRepositoryInterface $userRepository;
    private MessageBusInterface $eventBus;
    private UpdateUserPreferencesHandler $handler;
    
    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->eventBus = $this->createMock(MessageBusInterface::class);
        
        $this->handler = new UpdateUserPreferencesHandler(
            $this->userRepository,
            $this->eventBus
        );
    }
    
    public function testUpdatePreferencesSuccess(): void
    {
        // Arrange
        $userId = UserId::generate();
        $user = new User(
            $userId,
            new Email('test@example.com'),
            HashedPassword::fromPlainPassword('SecurePass123'),
            'John',
            'Doe'
        );
        
        $command = new UpdateUserPreferences(
            userId: $userId->getValue(),
            reportingCurrency: 'EUR',
            timezone: 'Europe/Paris',
            language: 'fr',
            theme: 'dark'
        );
        
        $this->userRepository
            ->expects($this->once())
            ->method('findById')
            ->with($userId)
            ->willReturn($user);
        
        $this->userRepository
            ->expects($this->once())
            ->method('save')
            ->with($user);
        
        $this->eventBus
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(UserPreferencesUpdated::class))
            ->willReturn(new Envelope(new \stdClass()));
        
        // Act
        $result = ($this->handler)($command);
        
        // Assert
        $this->assertEquals('EUR', $result->reportingCurrency);
        $this->assertEquals('Europe/Paris', $result->timezone);
        $this->assertEquals('fr', $result->language);
        $this->assertEquals('dark', $result->theme);
        
        // Verify user preferences were updated
        $preferences = $user->getPreferences();
        $this->assertEquals('EUR', $preferences->reportingCurrency);
        $this->assertEquals('Europe/Paris', $preferences->timezone);
    }
    
    public function testUpdatePartialPreferences(): void
    {
        // Arrange
        $userId = UserId::generate();
        $user = new User(
            $userId,
            new Email('test@example.com'),
            HashedPassword::fromPlainPassword('SecurePass123'),
            'John',
            'Doe'
        );
        
        // Only update currency
        $command = new UpdateUserPreferences(
            userId: $userId->getValue(),
            reportingCurrency: 'BTC'
        );
        
        $this->userRepository
            ->expects($this->once())
            ->method('findById')
            ->willReturn($user);
        
        $this->userRepository
            ->expects($this->once())
            ->method('save');
        
        $this->eventBus
            ->expects($this->once())
            ->method('dispatch')
            ->willReturn(new Envelope(new \stdClass()));
        
        // Act
        $result = ($this->handler)($command);
        
        // Assert
        $this->assertEquals('BTC', $result->reportingCurrency);
        // Other values should remain default
        $this->assertEquals('UTC', $result->timezone);
        $this->assertEquals('en', $result->language);
    }
    
    public function testUpdatePreferencesUserNotFound(): void
    {
        // Arrange
        $userId = UserId::generate();
        $command = new UpdateUserPreferences(
            userId: $userId->getValue(),
            reportingCurrency: 'EUR'
        );
        
        $this->userRepository
            ->expects($this->once())
            ->method('findById')
            ->willReturn(null);
        
        // Assert
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('User not found');
        
        // Act
        ($this->handler)($command);
    }
    
    public function testUpdatePreferencesInvalidCurrency(): void
    {
        // Arrange
        $userId = UserId::generate();
        $user = new User(
            $userId,
            new Email('test@example.com'),
            HashedPassword::fromPlainPassword('SecurePass123'),
            'John',
            'Doe'
        );
        
        $command = new UpdateUserPreferences(
            userId: $userId->getValue(),
            reportingCurrency: 'INVALID'
        );
        
        $this->userRepository
            ->expects($this->once())
            ->method('findById')
            ->willReturn($user);
        
        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported currency');
        
        // Act
        ($this->handler)($command);
    }
}
