<?php

declare(strict_types=1);

namespace App\Tests\Identity\Application\Handler;

use App\Identity\Application\Command\DisableMfa;
use App\Identity\Application\Handler\DisableMfaHandler;
use App\Identity\Domain\Event\MfaDisabled;
use App\Identity\Domain\Model\User;
use App\Identity\Domain\Repository\UserRepositoryInterface;
use App\Identity\Domain\ValueObject\Email;
use App\Identity\Domain\ValueObject\HashedPassword;
use App\Identity\Domain\ValueObject\UserId;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class DisableMfaHandlerTest extends TestCase
{
    private UserRepositoryInterface $userRepository;
    private MessageBusInterface $eventBus;
    private DisableMfaHandler $handler;
    
    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->eventBus = $this->createMock(MessageBusInterface::class);
        
        $this->handler = new DisableMfaHandler(
            $this->userRepository,
            $this->eventBus
        );
    }
    
    public function testDisableMfaSuccess(): void
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
        
        // Enable MFA first
        $user->enableMfa('JBSWY3DPEHPK3PXP');
        $this->assertTrue($user->isMfaEnabled());
        
        $command = new DisableMfa(userId: $userId->getValue());
        
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
            ->with($this->isInstanceOf(MfaDisabled::class))
            ->willReturn(new Envelope(new \stdClass()));
        
        // Act
        ($this->handler)($command);
        
        // Assert
        $this->assertFalse($user->isMfaEnabled());
        $this->assertNull($user->getMfaSecret());
    }
    
    public function testDisableMfaUserNotFound(): void
    {
        // Arrange
        $userId = UserId::generate();
        $command = new DisableMfa(userId: $userId->getValue());
        
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
    
    public function testDisableMfaNotEnabled(): void
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
        
        // MFA not enabled
        $this->assertFalse($user->isMfaEnabled());
        
        $command = new DisableMfa(userId: $userId->getValue());
        
        $this->userRepository
            ->expects($this->once())
            ->method('findById')
            ->willReturn($user);
        
        // Assert
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('MFA is not enabled');
        
        // Act
        ($this->handler)($command);
    }
}
