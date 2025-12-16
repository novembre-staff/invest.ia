<?php

declare(strict_types=1);

namespace App\Portfolio\Infrastructure\Adapter;

use App\Portfolio\Domain\Model\Portfolio;
use App\Portfolio\Domain\Model\Trade;

/**
 * Interface for fetching portfolio data from exchanges
 */
interface PortfolioProviderInterface
{
    /**
     * Get user's portfolio from exchange
     */
    public function getPortfolio(
        string $exchangeName,
        string $apiKey,
        string $apiSecret
    ): Portfolio;

    /**
     * Get trade history for a specific symbol
     * 
     * @return Trade[]
     */
    public function getTradeHistory(
        string $exchangeName,
        string $apiKey,
        string $apiSecret,
        string $symbol,
        int $limit = 100
    ): array;

    /**
     * Get all recent trades
     * 
     * @return Trade[]
     */
    public function getAllRecentTrades(
        string $exchangeName,
        string $apiKey,
        string $apiSecret,
        int $limit = 50
    ): array;
}
