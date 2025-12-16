<?php

declare(strict_types=1);

namespace App\Portfolio\Application\DTO;

use App\Portfolio\Domain\Model\Trade;

final readonly class TradeDTO
{
    private function __construct(
        public string $orderId,
        public string $symbol,
        public string $side,
        public float $price,
        public float $quantity,
        public float $quoteQuantity,
        public float $commission,
        public string $commissionAsset,
        public string $executedAt
    ) {
    }

    public static function fromDomain(Trade $trade): self
    {
        return new self(
            orderId: $trade->getOrderId(),
            symbol: $trade->getSymbol(),
            side: $trade->getSide(),
            price: $trade->getPrice(),
            quantity: $trade->getQuantity(),
            quoteQuantity: $trade->getQuoteQuantity(),
            commission: $trade->getCommission(),
            commissionAsset: $trade->getCommissionAsset(),
            executedAt: $trade->getExecutedAt()->format(\DateTimeInterface::ATOM)
        );
    }
}
