<?php

declare(strict_types=1);

namespace App\Portfolio\Domain\Model;

/**
 * Trade/Order from exchange history
 */
class Trade
{
    public function __construct(
        private readonly string $orderId,
        private readonly string $symbol,
        private readonly string $side, // BUY or SELL
        private readonly float $price,
        private readonly float $quantity,
        private readonly float $quoteQuantity,
        private readonly float $commission,
        private readonly string $commissionAsset,
        private readonly \DateTimeImmutable $executedAt,
        private readonly bool $isBuyer
    ) {
    }

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    public function getSide(): string
    {
        return $this->side;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getQuantity(): float
    {
        return $this->quantity;
    }

    public function getQuoteQuantity(): float
    {
        return $this->quoteQuantity;
    }

    public function getCommission(): float
    {
        return $this->commission;
    }

    public function getCommissionAsset(): string
    {
        return $this->commissionAsset;
    }

    public function getExecutedAt(): \DateTimeImmutable
    {
        return $this->executedAt;
    }

    public function isBuy(): bool
    {
        return $this->isBuyer;
    }

    public function isSell(): bool
    {
        return !$this->isBuyer;
    }
}
