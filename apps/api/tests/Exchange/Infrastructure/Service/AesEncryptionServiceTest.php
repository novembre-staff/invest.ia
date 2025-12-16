<?php

declare(strict_types=1);

namespace App\Tests\Exchange\Infrastructure\Service;

use App\Exchange\Infrastructure\Service\AesEncryptionService;
use PHPUnit\Framework\TestCase;

class AesEncryptionServiceTest extends TestCase
{
    private AesEncryptionService $encryptionService;

    protected function setUp(): void
    {
        // 32-character key for AES-256
        $this->encryptionService = new AesEncryptionService(
            'this-is-a-32-character-test-key-for-aes-256'
        );
    }

    public function testEncryptAndDecrypt(): void
    {
        $plainText = 'sensitive-api-key-12345';
        
        $encrypted = $this->encryptionService->encrypt($plainText);
        $decrypted = $this->encryptionService->decrypt($encrypted);
        
        $this->assertNotEquals($plainText, $encrypted);
        $this->assertEquals($plainText, $decrypted);
    }

    public function testEncryptedValueIsDifferentEachTime(): void
    {
        $plainText = 'test-value';
        
        $encrypted1 = $this->encryptionService->encrypt($plainText);
        $encrypted2 = $this->encryptionService->encrypt($plainText);
        
        // Should be different due to random IV
        $this->assertNotEquals($encrypted1, $encrypted2);
        
        // But both should decrypt to the same value
        $this->assertEquals($plainText, $this->encryptionService->decrypt($encrypted1));
        $this->assertEquals($plainText, $this->encryptionService->decrypt($encrypted2));
    }

    public function testDecryptInvalidData(): void
    {
        $this->expectException(\RuntimeException::class);
        
        $this->encryptionService->decrypt('invalid-base64-data!!!');
    }

    public function testDecryptCorruptedData(): void
    {
        $this->expectException(\RuntimeException::class);
        
        // Valid base64 but invalid encrypted data
        $this->encryptionService->decrypt(base64_encode('corrupted data'));
    }

    public function testShortKeyThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Encryption key must be at least 32 characters');
        
        new AesEncryptionService('short-key');
    }

    public function testEncryptEmptyString(): void
    {
        $encrypted = $this->encryptionService->encrypt('');
        $decrypted = $this->encryptionService->decrypt($encrypted);
        
        $this->assertEquals('', $decrypted);
    }

    public function testEncryptLongString(): void
    {
        $plainText = str_repeat('Lorem ipsum dolor sit amet, ', 100);
        
        $encrypted = $this->encryptionService->encrypt($plainText);
        $decrypted = $this->encryptionService->decrypt($encrypted);
        
        $this->assertEquals($plainText, $decrypted);
    }
}
