<?php

declare(strict_types=1);

namespace App\Exchange\Domain\Service;

/**
 * Service for encrypting/decrypting sensitive data like API credentials
 */
interface EncryptionServiceInterface
{
    /**
     * Encrypt a string value
     */
    public function encrypt(string $value): string;

    /**
     * Decrypt an encrypted string value
     */
    public function decrypt(string $encryptedValue): string;
}
