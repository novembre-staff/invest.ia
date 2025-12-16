<?php

declare(strict_types=1);

namespace App\Exchange\Domain\Model;

use App\Exchange\Domain\ValueObject\ApiCredentials;
use App\Exchange\Domain\ValueObject\ExchangeConnectionId;
use App\Identity\Domain\ValueObject\UserId;

/**
 * ExchangeConnection Aggregate Root
 * Represents a user's connection to a cryptocurrency exchange (e.g., Binance)
 */
class ExchangeConnection
{
    private \DateTimeImmutable $connectedAt;
    private ?\DateTimeImmutable $lastSyncAt = null;
    private bool $isActive = true;

    public function __construct(
        private readonly ExchangeConnectionId $id,
        private readonly UserId $userId,
        private readonly string $exchangeName,
        private ApiCredentials $credentials,
        private ?string $label = null
    ) {
        if (empty($exchangeName)) {
            throw new \InvalidArgumentException('Exchange name cannot be empty');
        }

        $this->connectedAt = new \DateTimeImmutable();
    }

    public function getId(): ExchangeConnectionId
    {
        return $this->id;
    }

    public function getUserId(): UserId
    {
        return $this->userId;
    }

    public function getExchangeName(): string
    {
        return $this->exchangeName;
    }

    public function getCredentials(): ApiCredentials
    {
        return $this->credentials;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function getConnectedAt(): \DateTimeImmutable
    {
        return $this->connectedAt;
    }

    public function getLastSyncAt(): ?\DateTimeImmutable
    {
        return $this->lastSyncAt;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * Update credentials (e.g., after API key rotation)
     */
    public function updateCredentials(ApiCredentials $credentials): void
    {
        $this->credentials = $credentials;
    }

    /**
     * Update the label for this connection
     */
    public function updateLabel(?string $label): void
    {
        $this->label = $label;
    }

    /**
     * Mark this connection as synchronized
     */
    public function markAsSynced(): void
    {
        $this->lastSyncAt = new \DateTimeImmutable();
    }

    /**
     * Deactivate this connection
     */
    public function deactivate(): void
    {
        $this->isActive = false;
    }

    /**
     * Reactivate this connection
     */
    public function reactivate(): void
    {
        $this->isActive = true;
    }
}
