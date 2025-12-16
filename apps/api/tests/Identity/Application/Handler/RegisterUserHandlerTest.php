<?php

declare(strict_types=1);

namespace App\Tests\Identity\Application\Handler;

use App\Identity\Application\Command\RegisterUser;
use App\Identity\Application\DTO\UserDTO;
use App\Identity\Application\Handler\RegisterUserHandler;
use App\Identity\Domain\Event\UserRegistered;
use App\Identity\Domain\Model\User;
use App\Identity\Domain\Repository\UserRepositoryInterface;
use App\Shared\Application\Service\PasswordHasherFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherInterface;

class RegisterUserHandlerTest extends TestCase
{
    private UserRepositoryInterface $userRepository;
    private PasswordHasherFactory $passwordHasherFactory;
    private MessageBusInterface $eventBus;
    private RegisterUserHandler $handler;
    
    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->passwordHasherFactory = $this->createMock(PasswordHasherFactory::class);
        $this->eventBus = $this->createMock(MessageBusInterface::class);
        
        $this->handler = new RegisterUserHandler(
            $this->userRepository,
            $this->passwordHasherFactory,
            $this->eventBus
        );
    }
    
    public function testRegisterUserSuccess(): void
    {
        // Arrange
        $command = new RegisterUser(
            email: 'test@example.com',
            password: 'SecurePass123',
            firstName: 'John',
            lastName: 'Doe'
        );
        
        $this->userRepository
            ->expects($this->once())
            ->method('emailExists')
            ->willReturn(false);
        
        $passwordHasher = $this->createMock(PasswordHasherInterface::class);
        $passwordHasher
            ->expects($this->once())
            ->method('hash')
            ->willReturn('$2y$12$hashedpassword');
        
        $this->passwordHasherFactory
            ->expects($this->once())
            ->method('createHasher')
            ->willReturn($passwordHasher);
        
        $this->userRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(User::class));
        
        $this->eventBus
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(UserRegistered::class))
            ->willReturn(new Envelope(new \stdClass()));
        
        // Act
        $result = ($this->handler)($command);
        
        // Assert
        $this->assertInstanceOf(UserDTO::class, $result);
        $this->assertEquals('test@example.com', $result->email);
        $this->assertEquals('John', $result->firstName);
        $this->assertEquals('Doe', $result->lastName);
        $this->assertEquals('pending_verification', $result->status);
        $this->assertFalse($result->mfaEnabled);
    }
    
    public function testRegisterUserWithExistingEmail(): void
    {
        // Arrange
        $command = new RegisterUser(
            email: 'existing@example.com',
            password: 'SecurePass123',
            firstName: 'John',
            lastName: 'Doe'
        );
        
        $this->userRepository
            ->expects($this->once())
            ->method('emailExists')
            ->willReturn(true);
        
        // Assert
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Email already registered');
        
        // Act
        ($this->handler)($command);
    }
}
