<?php

declare(strict_types=1);

namespace App\Trading\Infrastructure\Adapter;

use App\Trading\Domain\Model\Order;

interface OrderExecutorInterface
{
    /**
     * Execute an order on the exchange
     * @return array Exchange response data
     * @throws \RuntimeException if execution fails
     */
    public function execute(Order $order, string $apiKey, string $apiSecret): array;

    /**
     * Cancel an order on the exchange
     * @throws \RuntimeException if cancellation fails
     */
    public function cancel(Order $order, string $apiKey, string $apiSecret): void;

    /**
     * Get order status from exchange
     * @return array|null Order data or null if not found
     */
    public function getOrderStatus(string $symbol, string $exchangeOrderId, string $apiKey, string $apiSecret): ?array;
}
