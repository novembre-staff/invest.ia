<?php

declare(strict_types=1);

namespace App\Exchange\Infrastructure\Adapter;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Binance API client implementation
 * https://binance-docs.github.io/apidocs/spot/en/
 */
final readonly class BinanceApiClient implements ExchangeApiClientInterface
{
    private const EXCHANGE_NAME = 'binance';
    private const BASE_URL = 'https://api.binance.com';

    public function __construct(
        private HttpClientInterface $httpClient
    ) {
    }

    public function supports(string $exchangeName): bool
    {
        return strtolower($exchangeName) === self::EXCHANGE_NAME;
    }

    public function validateCredentials(
        string $exchangeName,
        string $apiKey,
        string $apiSecret
    ): bool {
        if (!$this->supports($exchangeName)) {
            throw new \InvalidArgumentException("Unsupported exchange: {$exchangeName}");
        }

        try {
            // Test credentials by fetching account information
            $this->getAccountBalance($exchangeName, $apiKey, $apiSecret);
            return true;
        } catch (\Exception) {
            return false;
        }
    }

    public function getAccountBalance(
        string $exchangeName,
        string $apiKey,
        string $apiSecret
    ): array {
        if (!$this->supports($exchangeName)) {
            throw new \InvalidArgumentException("Unsupported exchange: {$exchangeName}");
        }

        $timestamp = (int)(microtime(true) * 1000);
        $queryString = "timestamp={$timestamp}";
        $signature = hash_hmac('sha256', $queryString, $apiSecret);

        $response = $this->httpClient->request('GET', self::BASE_URL . '/api/v3/account', [
            'query' => [
                'timestamp' => $timestamp,
                'signature' => $signature,
            ],
            'headers' => [
                'X-MBX-APIKEY' => $apiKey,
            ],
        ]);

        $data = $response->toArray();

        // Extract balances
        $balances = [];
        foreach ($data['balances'] ?? [] as $balance) {
            $free = (float)$balance['free'];
            $locked = (float)$balance['locked'];
            $total = $free + $locked;

            if ($total > 0) {
                $balances[$balance['asset']] = $total;
            }
        }

        return $balances;
    }
}
