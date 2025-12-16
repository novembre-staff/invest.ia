<?php

declare(strict_types=1);

namespace App\Market\Application\DTO;

use App\Market\Domain\Model\MarketData;

final readonly class MarketDataDTO
{
    private function __construct(
        public string $symbol,
        public float $currentPrice,
        public float $highPrice24h,
        public float $lowPrice24h,
        public float $openPrice24h,
        public string $volume24h,
        public float $priceChangePercent24h,
        public string $currency,
        public string $timestamp,
        public bool $isPositiveChange
    ) {
    }

    public static function fromDomain(MarketData $marketData): self
    {
        return new self(
            symbol: $marketData->getSymbol()->getValue(),
            currentPrice: $marketData->getCurrentPrice()->getValue(),
            highPrice24h: $marketData->getHighPrice24h()->getValue(),
            lowPrice24h: $marketData->getLowPrice24h()->getValue(),
            openPrice24h: $marketData->getOpenPrice24h()->getValue(),
            volume24h: $marketData->getVolume24h()->format(),
            priceChangePercent24h: $marketData->getPriceChangePercent24h(),
            currency: $marketData->getCurrentPrice()->getCurrency(),
            timestamp: $marketData->getTimestamp()->format(\DateTimeInterface::ATOM),
            isPositiveChange: $marketData->isPositiveChange()
        );
    }
}
