<?php

declare(strict_types=1);

namespace App\Exchange\Infrastructure\Service;

use App\Exchange\Domain\Service\EncryptionServiceInterface;

/**
 * AES-256-CBC encryption service for sensitive data
 */
final readonly class AesEncryptionService implements EncryptionServiceInterface
{
    public function __construct(
        private string $encryptionKey
    ) {
        if (strlen($encryptionKey) < 32) {
            throw new \InvalidArgumentException('Encryption key must be at least 32 characters');
        }
    }

    public function encrypt(string $value): string
    {
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt(
            $value,
            'aes-256-cbc',
            $this->encryptionKey,
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($encrypted === false) {
            throw new \RuntimeException('Encryption failed');
        }

        // Prepend IV to encrypted data and encode as base64
        return base64_encode($iv . $encrypted);
    }

    public function decrypt(string $encryptedValue): string
    {
        $data = base64_decode($encryptedValue, true);

        if ($data === false) {
            throw new \RuntimeException('Invalid encrypted data format');
        }

        // Extract IV and encrypted data
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);

        $decrypted = openssl_decrypt(
            $encrypted,
            'aes-256-cbc',
            $this->encryptionKey,
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($decrypted === false) {
            throw new \RuntimeException('Decryption failed');
        }

        return $decrypted;
    }
}
