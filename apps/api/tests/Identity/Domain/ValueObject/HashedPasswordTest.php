<?php

declare(strict_types=1);

namespace App\Tests\Identity\Domain\ValueObject;

use App\Identity\Domain\ValueObject\HashedPassword;
use PHPUnit\Framework\TestCase;

class HashedPasswordTest extends TestCase
{
    public function testValidPassword(): void
    {
        $password = HashedPassword::fromPlainPassword('SecurePass123');
        
        $this->assertNotEmpty($password->getHash());
        $this->assertStringStartsWith('$2y$', $password->getHash()); // bcrypt
    }
    
    public function testPasswordTooShort(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Password must be at least 8 characters');
        
        HashedPassword::fromPlainPassword('Short1');
    }
    
    public function testPasswordTooLong(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Password cannot exceed 72 characters');
        
        $longPassword = str_repeat('a', 73);
        HashedPassword::fromPlainPassword($longPassword);
    }
    
    public function testPasswordMissingUppercase(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Password must contain at least one uppercase letter');
        
        HashedPassword::fromPlainPassword('securepass123');
    }
    
    public function testPasswordMissingLowercase(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Password must contain at least one lowercase letter');
        
        HashedPassword::fromPlainPassword('SECUREPASS123');
    }
    
    public function testPasswordMissingDigit(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Password must contain at least one digit');
        
        HashedPassword::fromPlainPassword('SecurePass');
    }
    
    public function testFromHash(): void
    {
        $hash = password_hash('SecurePass123', PASSWORD_BCRYPT);
        $password = HashedPassword::fromHash($hash);
        
        $this->assertEquals($hash, $password->getHash());
    }
    
    public function testToStringIsProtected(): void
    {
        $password = HashedPassword::fromPlainPassword('SecurePass123');
        
        $this->assertEquals('[PROTECTED]', (string) $password);
    }
}
