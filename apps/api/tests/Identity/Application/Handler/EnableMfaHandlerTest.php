<?php

declare(strict_types=1);

namespace App\Tests\Identity\Application\Handler;

use App\Identity\Application\Command\EnableMfa;
use App\Identity\Application\Handler\EnableMfaHandler;
use App\Identity\Domain\Event\MfaEnabled;
use App\Identity\Domain\Model\User;
use App\Identity\Domain\Repository\UserRepositoryInterface;
use App\Identity\Domain\Service\TotpService;
use App\Identity\Domain\ValueObject\Email;
use App\Identity\Domain\ValueObject\HashedPassword;
use App\Identity\Domain\ValueObject\UserId;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class EnableMfaHandlerTest extends TestCase
{
    private UserRepositoryInterface $userRepository;
    private TotpService $totpService;
    private MessageBusInterface $eventBus;
    private EnableMfaHandler $handler;
    
    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->totpService = new TotpService(); // Use real service for testing
        $this->eventBus = $this->createMock(MessageBusInterface::class);
        
        $this->handler = new EnableMfaHandler(
            $this->userRepository,
            $this->totpService,
            $this->eventBus
        );
    }
    
    public function testEnableMfaSuccess(): void
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
        
        // Verify email to activate user
        $user->verifyEmail();
        
        $command = new EnableMfa(userId: $userId->getValue());
        
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
            ->with($this->isInstanceOf(MfaEnabled::class))
            ->willReturn(new Envelope(new \stdClass()));
        
        // Act
        $result = ($this->handler)($command);
        
        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('secret', $result);
        $this->assertArrayHasKey('qrCodeUri', $result);
        $this->assertArrayHasKey('qrCodeUrl', $result);
        
        // Verify secret format (base32)
        $this->assertMatchesRegularExpression('/^[A-Z2-7]+$/', $result['secret']);
        
        // Verify QR code URI format
        $this->assertStringStartsWith('otpauth://totp/', $result['qrCodeUri']);
        $this->assertStringContainsString('test@example.com', $result['qrCodeUri']);
        
        // Verify QR code URL format
        $this->assertStringStartsWith('https://chart.googleapis.com/chart', $result['qrCodeUrl']);
        
        // Verify user has MFA enabled
        $this->assertTrue($user->isMfaEnabled());
        $this->assertEquals($result['secret'], $user->getMfaSecret());
    }
    
    public function testEnableMfaUserNotFound(): void
    {
        // Arrange
        $userId = UserId::generate();
        $command = new EnableMfa(userId: $userId->getValue());
        
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
    
    public function testEnableMfaAlreadyEnabled(): void
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
        
        $command = new EnableMfa(userId: $userId->getValue());
        
        $this->userRepository
            ->expects($this->once())
            ->method('findById')
            ->willReturn($user);
        
        // Assert
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('MFA is already enabled');
        
        // Act
        ($this->handler)($command);
    }
}
