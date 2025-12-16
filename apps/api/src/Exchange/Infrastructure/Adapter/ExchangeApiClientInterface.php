<?php

declare(strict_types=1);

namespace App\Exchange\Infrastructure\Adapter;

/**
 * Interface for exchange API clients (Binance, Coinbase, etc.)
 */
interface ExchangeApiClientInterface
{
    /**
     * Validate API credentials by making a test request
     */
    public function validateCredentials(
        string $exchangeName,
        string $apiKey,
        string $apiSecret
    ): bool;

    /**
     * Get account balance from the exchange
     * 
     * @return array<string, float> Map of currency to balance
     */
    public function getAccountBalance(
        string $exchangeName,
        string $apiKey,
        string $apiSecret
    ): array;

    /**
     * Test if the API client supports a specific exchange
     */
    public function supports(string $exchangeName): bool;
}
