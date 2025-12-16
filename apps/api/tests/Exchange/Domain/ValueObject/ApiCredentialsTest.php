<?php

declare(strict_types=1);

namespace App\Tests\Exchange\Domain\ValueObject;

use App\Exchange\Domain\ValueObject\ApiCredentials;
use PHPUnit\Framework\TestCase;

class ApiCredentialsTest extends TestCase
{
    public function testCreatePlainCredentials(): void
    {
        $credentials = ApiCredentials::plain('test-api-key', 'test-api-secret');
        
        $this->assertEquals('test-api-key', $credentials->getApiKey());
        $this->assertEquals('test-api-secret', $credentials->getApiSecret());
        $this->assertFalse($credentials->isEncrypted());
    }
    
    public function testCreateEncryptedCredentials(): void
    {
        $credentials = ApiCredentials::encrypted('encrypted-key', 'encrypted-secret');
        
        $this->assertEquals('encrypted-key', $credentials->getApiKey());
        $this->assertEquals('encrypted-secret', $credentials->getApiSecret());
        $this->assertTrue($credentials->isEncrypted());
    }
    
    public function testEmptyApiKeyThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('API key cannot be empty');
        
        ApiCredentials::plain('', 'secret');
    }
    
    public function testEmptyApiSecretThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('API secret cannot be empty');
        
        ApiCredentials::plain('key', '');
    }
    
    public function testEquals(): void
    {
        $creds1 = ApiCredentials::plain('key1', 'secret1');
        $creds2 = ApiCredentials::plain('key1', 'secret1');
        $creds3 = ApiCredentials::plain('key2', 'secret2');
        $creds4 = ApiCredentials::encrypted('key1', 'secret1');
        
        $this->assertTrue($creds1->equals($creds2));
        $this->assertFalse($creds1->equals($creds3));
        $this->assertFalse($creds1->equals($creds4)); // Different encryption state
    }
}
