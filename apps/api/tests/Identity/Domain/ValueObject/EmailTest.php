<?php

declare(strict_types=1);

namespace App\Tests\Identity\Domain\ValueObject;

use App\Identity\Domain\ValueObject\Email;
use PHPUnit\Framework\TestCase;

class EmailTest extends TestCase
{
    public function testValidEmail(): void
    {
        $email = new Email('test@example.com');
        
        $this->assertEquals('test@example.com', $email->getValue());
    }
    
    public function testEmailIsNormalized(): void
    {
        $email = new Email('Test@EXAMPLE.COM');
        
        $this->assertEquals('test@example.com', $email->getValue());
    }
    
    public function testInvalidEmailFormat(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email format');
        
        new Email('invalid-email');
    }
    
    public function testEmptyEmail(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        new Email('');
    }
    
    public function testEmailTooLong(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Email cannot exceed 255 characters');
        
        $longEmail = str_repeat('a', 250) . '@test.com';
        new Email($longEmail);
    }
    
    public function testEmailEquality(): void
    {
        $email1 = new Email('test@example.com');
        $email2 = new Email('TEST@EXAMPLE.COM');
        $email3 = new Email('other@example.com');
        
        $this->assertTrue($email1->equals($email2));
        $this->assertFalse($email1->equals($email3));
    }
}
