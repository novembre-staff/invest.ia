<?php

declare(strict_types=1);

namespace App\Market\Domain\Model;

use App\Market\Domain\ValueObject\Price;
use App\Market\Domain\ValueObject\Symbol;
use App\Market\Domain\ValueObject\Volume;

/**
 * MarketData represents real-time market information for a trading pair
 * This is a read model, not an aggregate (data comes from external source)
 */
class MarketData
{
    public function __construct(
        private readonly Symbol $symbol,
        private readonly Price $currentPrice,
        private readonly Price $highPrice24h,
        private readonly Price $lowPrice24h,
        private readonly Price $openPrice24h,
        private readonly Volume $volume24h,
        private readonly float $priceChangePercent24h,
        private readonly \DateTimeImmutable $timestamp
    ) {
    }

    public function getSymbol(): Symbol
    {
        return $this->symbol;
    }

    public function getCurrentPrice(): Price
    {
        return $this->currentPrice;
    }

    public function getHighPrice24h(): Price
    {
        return $this->highPrice24h;
    }

    public function getLowPrice24h(): Price
    {
        return $this->lowPrice24h;
    }

    public function getOpenPrice24h(): Price
    {
        return $this->openPrice24h;
    }

    public function getVolume24h(): Volume
    {
        return $this->volume24h;
    }

    public function getPriceChangePercent24h(): float
    {
        return $this->priceChangePercent24h;
    }

    public function getTimestamp(): \DateTimeImmutable
    {
        return $this->timestamp;
    }

    public function isPositiveChange(): bool
    {
        return $this->priceChangePercent24h > 0;
    }

    /**
     * Check if price is near 24h high (within 5%)
     */
    public function isNearHigh(): bool
    {
        $threshold = $this->highPrice24h->getValue() * 0.95;
        return $this->currentPrice->getValue() >= $threshold;
    }

    /**
     * Check if price is near 24h low (within 5%)
     */
    public function isNearLow(): bool
    {
        $threshold = $this->lowPrice24h->getValue() * 1.05;
        return $this->currentPrice->getValue() <= $threshold;
    }
}
