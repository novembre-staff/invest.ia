<?php

declare(strict_types=1);

namespace App\Trading\Application\DTO;

use App\Trading\Domain\Model\Order;

final readonly class OrderDTO
{
    public function __construct(
        public string $id,
        public string $userId,
        public string $symbol,
        public string $type,
        public string $side,
        public string $status,
        public float $quantity,
        public ?float $price,
        public ?float $stopPrice,
        public string $timeInForce,
        public float $executedQuantity,
        public float $cumulativeQuoteQuantity,
        public ?float $averagePrice,
        public float $filledPercentage,
        public float $remainingQuantity,
        public ?string $exchangeOrderId,
        public ?string $executedAt,
        public ?string $rejectReason,
        public string $createdAt,
        public string $updatedAt
    ) {
    }

    public static function fromDomain(Order $order): self
    {
        return new self(
            $order->getId()->getValue(),
            $order->getUserId()->getValue(),
            $order->getSymbol(),
            $order->getType()->value,
            $order->getSide()->value,
            $order->getStatus()->value,
            $order->getQuantity(),
            $order->getPrice(),
            $order->getStopPrice(),
            $order->getTimeInForce()->value,
            $order->getExecutedQuantity(),
            $order->getCumulativeQuoteQuantity(),
            $order->getAveragePrice(),
            $order->getFilledPercentage(),
            $order->getRemainingQuantity(),
            $order->getExchangeOrderId(),
            $order->getExecutedAt()?->format(\DateTimeInterface::ATOM),
            $order->getRejectReason(),
            $order->getCreatedAt()->format(\DateTimeInterface::ATOM),
            $order->getUpdatedAt()->format(\DateTimeInterface::ATOM)
        );
    }
}
