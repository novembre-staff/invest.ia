<?php

declare(strict_types=1);

namespace App\Portfolio\Infrastructure\Adapter;

use App\Exchange\Domain\Service\EncryptionServiceInterface;
use App\Portfolio\Domain\Model\Portfolio;
use App\Portfolio\Domain\Model\Trade;
use App\Portfolio\Domain\ValueObject\AssetBalance;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Binance portfolio adapter
 */
final readonly class BinancePortfolioProvider implements PortfolioProviderInterface
{
    private const BASE_URL = 'https://api.binance.com';

    public function __construct(
        private HttpClientInterface $httpClient,
        private EncryptionServiceInterface $encryptionService
    ) {
    }

    public function getPortfolio(
        string $exchangeName,
        string $encryptedApiKey,
        string $encryptedApiSecret
    ): Portfolio {
        // Decrypt credentials
        $apiKey = $this->encryptionService->decrypt($encryptedApiKey);
        $apiSecret = $this->encryptionService->decrypt($encryptedApiSecret);

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

        // Map balances
        $balances = [];
        foreach ($data['balances'] ?? [] as $balanceData) {
            $balance = new AssetBalance(
                asset: $balanceData['asset'],
                free: (float)$balanceData['free'],
                locked: (float)$balanceData['locked']
            );

            $balances[] = $balance;
        }

        return new Portfolio(
            exchangeName: $exchangeName,
            userId: '', // Will be set by handler
            balances: $balances,
            lastUpdated: new \DateTimeImmutable()
        );
    }

    public function getTradeHistory(
        string $exchangeName,
        string $encryptedApiKey,
        string $encryptedApiSecret,
        string $symbol,
        int $limit = 100
    ): array {
        $apiKey = $this->encryptionService->decrypt($encryptedApiKey);
        $apiSecret = $this->encryptionService->decrypt($encryptedApiSecret);

        $timestamp = (int)(microtime(true) * 1000);
        $queryString = "symbol={$symbol}&timestamp={$timestamp}&limit={$limit}";
        $signature = hash_hmac('sha256', $queryString, $apiSecret);

        $response = $this->httpClient->request('GET', self::BASE_URL . '/api/v3/myTrades', [
            'query' => [
                'symbol' => $symbol,
                'timestamp' => $timestamp,
                'limit' => $limit,
                'signature' => $signature,
            ],
            'headers' => [
                'X-MBX-APIKEY' => $apiKey,
            ],
        ]);

        $data = $response->toArray();

        return array_map(fn($tradeData) => $this->mapToTrade($tradeData), $data);
    }

    public function getAllRecentTrades(
        string $exchangeName,
        string $encryptedApiKey,
        string $encryptedApiSecret,
        int $limit = 50
    ): array {
        $apiKey = $this->encryptionService->decrypt($encryptedApiKey);
        $apiSecret = $this->encryptionService->decrypt($encryptedApiSecret);

        // Get all orders first (simplified approach)
        $timestamp = (int)(microtime(true) * 1000);
        $queryString = "timestamp={$timestamp}";
        $signature = hash_hmac('sha256', $queryString, $apiSecret);

        $response = $this->httpClient->request('GET', self::BASE_URL . '/api/v3/allOrders', [
            'query' => [
                'symbol' => 'BTCUSDT', // Start with BTC as example
                'timestamp' => $timestamp,
                'limit' => $limit,
                'signature' => $signature,
            ],
            'headers' => [
                'X-MBX-APIKEY' => $apiKey,
            ],
        ]);

        $data = $response->toArray();

        // Filter executed orders and map to trades
        $trades = [];
        foreach ($data as $orderData) {
            if ($orderData['status'] === 'FILLED') {
                $trades[] = new Trade(
                    orderId: (string)$orderData['orderId'],
                    symbol: $orderData['symbol'],
                    side: $orderData['side'],
                    price: (float)$orderData['price'],
                    quantity: (float)$orderData['executedQty'],
                    quoteQuantity: (float)$orderData['cummulativeQuoteQty'],
                    commission: 0, // Would need separate call for details
                    commissionAsset: '',
                    executedAt: new \DateTimeImmutable('@' . intval($orderData['time'] / 1000)),
                    isBuyer: $orderData['side'] === 'BUY'
                );
            }
        }

        return array_slice($trades, 0, $limit);
    }

    private function mapToTrade(array $data): Trade
    {
        return new Trade(
            orderId: (string)$data['orderId'],
            symbol: $data['symbol'],
            side: $data['isBuyer'] ? 'BUY' : 'SELL',
            price: (float)$data['price'],
            quantity: (float)$data['qty'],
            quoteQuantity: (float)$data['quoteQty'],
            commission: (float)$data['commission'],
            commissionAsset: $data['commissionAsset'],
            executedAt: new \DateTimeImmutable('@' . intval($data['time'] / 1000)),
            isBuyer: (bool)$data['isBuyer']
        );
    }
}
