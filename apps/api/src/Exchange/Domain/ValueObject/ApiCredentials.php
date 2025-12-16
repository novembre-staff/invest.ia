<?php

declare(strict_types=1);

namespace App\Exchange\Domain\ValueObject;

/**
 * Encrypted API credentials for exchange connections
 * ApiKey and ApiSecret are stored encrypted in the database
 */
final readonly class ApiCredentials
{
    public function __construct(
        private string $apiKey,
        private string $apiSecret,
        private bool $isEncrypted = false
    ) {
        if (empty($apiKey)) {
            throw new \InvalidArgumentException('API key cannot be empty');
        }
        
        if (empty($apiSecret)) {
            throw new \InvalidArgumentException('API secret cannot be empty');
        }
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function getApiSecret(): string
    {
        return $this->apiSecret;
    }

    public function isEncrypted(): bool
    {
        return $this->isEncrypted;
    }

    /**
     * Create encrypted credentials (used after encryption)
     */
    public static function encrypted(string $encryptedApiKey, string $encryptedApiSecret): self
    {
        return new self($encryptedApiKey, $encryptedApiSecret, true);
    }

    /**
     * Create plain credentials (used before encryption)
     */
    public static function plain(string $apiKey, string $apiSecret): self
    {
        return new self($apiKey, $apiSecret, false);
    }

    public function equals(self $other): bool
    {
        return $this->apiKey === $other->apiKey
            && $this->apiSecret === $other->apiSecret
            && $this->isEncrypted === $other->isEncrypted;
    }
}
