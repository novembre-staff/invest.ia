<?php

declare(strict_types=1);

namespace App\Trading\Infrastructure\Adapter;

use App\Trading\Domain\Model\Order;
use App\Trading\Domain\ValueObject\OrderType;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Binance order execution adapter
 * Integrates with Binance Spot API v3
 */
final readonly class BinanceOrderExecutor implements OrderExecutorInterface
{
    private const BASE_URL = 'https://api.binance.com';

    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger
    ) {
    }

    public function execute(Order $order, string $apiKey, string $apiSecret): array
    {
        $endpoint = '/api/v3/order';
        $params = $this->buildOrderParams($order);
        
        try {
            $response = $this->sendSignedRequest('POST', $endpoint, $params, $apiKey, $apiSecret);

            $this->logger->info('Order executed on Binance', [
                'orderId' => $order->getId()->getValue(),
                'symbol' => $order->getSymbol(),
                'exchangeOrderId' => $response['orderId'] ?? null,
            ]);

            return $response;
        } catch (\Exception $e) {
            $this->logger->error('Failed to execute order on Binance', [
                'orderId' => $order->getId()->getValue(),
                'error' => $e->getMessage(),
            ]);
            throw new \RuntimeException('Failed to execute order: ' . $e->getMessage(), 0, $e);
        }
    }

    public function cancel(Order $order, string $apiKey, string $apiSecret): void
    {
        if ($order->getExchangeOrderId() === null) {
            throw new \RuntimeException('Cannot cancel order without exchange order ID');
        }

        $endpoint = '/api/v3/order';
        $params = [
            'symbol' => $order->getSymbol(),
            'orderId' => $order->getExchangeOrderId(),
        ];

        try {
            $this->sendSignedRequest('DELETE', $endpoint, $params, $apiKey, $apiSecret);

            $this->logger->info('Order cancelled on Binance', [
                'orderId' => $order->getId()->getValue(),
                'exchangeOrderId' => $order->getExchangeOrderId(),
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to cancel order on Binance', [
                'orderId' => $order->getId()->getValue(),
                'error' => $e->getMessage(),
            ]);
            throw new \RuntimeException('Failed to cancel order: ' . $e->getMessage(), 0, $e);
        }
    }

    public function getOrderStatus(string $symbol, string $exchangeOrderId, string $apiKey, string $apiSecret): ?array
    {
        $endpoint = '/api/v3/order';
        $params = [
            'symbol' => $symbol,
            'orderId' => $exchangeOrderId,
        ];

        try {
            return $this->sendSignedRequest('GET', $endpoint, $params, $apiKey, $apiSecret);
        } catch (\Exception $e) {
            $this->logger->warning('Failed to get order status from Binance', [
                'symbol' => $symbol,
                'exchangeOrderId' => $exchangeOrderId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function buildOrderParams(Order $order): array
    {
        $params = [
            'symbol' => $order->getSymbol(),
            'side' => $order->getSide()->toBinanceSide(),
            'type' => $order->getType()->toBinanceType(),
            'quantity' => (string)$order->getQuantity(),
            'newClientOrderId' => $order->getId()->getValue(),
        ];

        // Add price for limit orders
        if ($order->getPrice() !== null) {
            $params['price'] = (string)$order->getPrice();
        }

        // Add stop price for stop orders
        if ($order->getStopPrice() !== null) {
            $params['stopPrice'] = (string)$order->getStopPrice();
        }

        // Add time in force for limit orders
        if ($order->getType() !== OrderType::MARKET) {
            $params['timeInForce'] = $order->getTimeInForce()->value;
        }

        return $params;
    }

    private function sendSignedRequest(
        string $method,
        string $endpoint,
        array $params,
        string $apiKey,
        string $apiSecret
    ): array {
        // Add timestamp
        $params['timestamp'] = (int)(microtime(true) * 1000);
        
        // Create signature
        $queryString = http_build_query($params);
        $signature = hash_hmac('sha256', $queryString, $apiSecret);
        $params['signature'] = $signature;

        $url = self::BASE_URL . $endpoint . '?' . http_build_query($params);

        $response = $this->httpClient->request($method, $url, [
            'headers' => [
                'X-MBX-APIKEY' => $apiKey,
            ],
        ]);

        $statusCode = $response->getStatusCode();
        $content = $response->getContent(false);

        if ($statusCode !== 200) {
            $error = json_decode($content, true);
            throw new \RuntimeException(
                $error['msg'] ?? 'Unknown Binance API error',
                $error['code'] ?? $statusCode
            );
        }

        return json_decode($content, true);
    }
}
