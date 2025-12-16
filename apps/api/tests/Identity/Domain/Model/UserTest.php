<?php

declare(strict_types=1);

namespace App\Tests\Identity\Domain\Model;

use App\Identity\Domain\Model\User;
use App\Identity\Domain\ValueObject\Email;
use App\Identity\Domain\ValueObject\HashedPassword;
use App\Identity\Domain\ValueObject\UserId;
use App\Identity\Domain\ValueObject\UserStatus;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testCreateUser(): void
    {
        $userId = UserId::generate();
        $email = new Email('test@example.com');
        $password = HashedPassword::fromPlainPassword('SecurePass123');
        
        $user = new User(
            $userId,
            $email,
            $password,
            'John',
            'Doe'
        );
        
        $this->assertEquals($userId, $user->getId());
        $this->assertEquals($email, $user->getEmail());
        $this->assertEquals('John', $user->getFirstName());
        $this->assertEquals('Doe', $user->getLastName());
        $this->assertEquals(UserStatus::PENDING_VERIFICATION, $user->getStatus());
        $this->assertFalse($user->isMfaEnabled());
        $this->assertNull($user->getEmailVerifiedAt());
    }
    
    public function testVerifyEmail(): void
    {
        $user = $this->createUser();
        $this->assertNull($user->getEmailVerifiedAt());
        
        $user->verifyEmail();
        
        $this->assertInstanceOf(\DateTimeImmutable::class, $user->getEmailVerifiedAt());
        $this->assertEquals(UserStatus::ACTIVE, $user->getStatus());
    }
    
    public function testEnableMfa(): void
    {
        $user = $this->createUser();
        $this->assertFalse($user->isMfaEnabled());
        
        $secret = 'JBSWY3DPEHPK3PXP';
        $user->enableMfa($secret);
        
        $this->assertTrue($user->isMfaEnabled());
        $this->assertEquals($secret, $user->getMfaSecret());
    }
    
    public function testDisableMfa(): void
    {
        $user = $this->createUser();
        $user->enableMfa('JBSWY3DPEHPK3PXP');
        
        $this->assertTrue($user->isMfaEnabled());
        
        $user->disableMfa();
        
        $this->assertFalse($user->isMfaEnabled());
        $this->assertNull($user->getMfaSecret());
    }
    
    public function testSuspendUser(): void
    {
        $user = $this->createUser();
        $user->verifyEmail();
        
        $this->assertEquals(UserStatus::ACTIVE, $user->getStatus());
        
        $user->suspend();
        
        $this->assertEquals(UserStatus::SUSPENDED, $user->getStatus());
    }
    
    public function testActivateUser(): void
    {
        $user = $this->createUser();
        $user->verifyEmail();
        $user->suspend();
        
        $this->assertEquals(UserStatus::SUSPENDED, $user->getStatus());
        
        $user->activate();
        
        $this->assertEquals(UserStatus::ACTIVE, $user->getStatus());
    }
    
    public function testDeleteUser(): void
    {
        $user = $this->createUser();
        
        $user->delete();
        
        $this->assertEquals(UserStatus::DELETED, $user->getStatus());
    }
    
    public function testChangePassword(): void
    {
        $user = $this->createUser();
        $oldPassword = $user->getPassword();
        
        $newPassword = HashedPassword::fromPlainPassword('NewSecurePass456');
        $user->changePassword($newPassword);
        
        $this->assertNotEquals($oldPassword, $user->getPassword());
        $this->assertEquals($newPassword, $user->getPassword());
    }
    
    private function createUser(): User
    {
        return new User(
            UserId::generate(),
            new Email('test@example.com'),
            HashedPassword::fromPlainPassword('SecurePass123'),
            'John',
            'Doe'
        );
    }
}
