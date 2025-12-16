<?php

declare(strict_types=1);

namespace App\Trading\Domain\Model;

use App\Identity\Domain\ValueObject\UserId;
use App\Trading\Domain\ValueObject\OrderId;
use App\Trading\Domain\ValueObject\OrderSide;
use App\Trading\Domain\ValueObject\OrderStatus;
use App\Trading\Domain\ValueObject\OrderType;
use App\Trading\Domain\ValueObject\TimeInForce;

class Order
{
    private OrderId $id;
    private UserId $userId;
    private string $symbol;
    private OrderType $type;
    private OrderSide $side;
    private OrderStatus $status;
    private float $quantity;
    private ?float $price;
    private ?float $stopPrice;
    private TimeInForce $timeInForce;
    private float $executedQuantity;
    private float $cumulativeQuoteQuantity;
    private ?string $exchangeOrderId;
    private ?string $clientOrderId;
    private ?\DateTimeImmutable $executedAt;
    private ?string $rejectReason;
    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;

    public function __construct(
        OrderId $id,
        UserId $userId,
        string $symbol,
        OrderType $type,
        OrderSide $side,
        float $quantity,
        ?float $price = null,
        ?float $stopPrice = null,
        TimeInForce $timeInForce = TimeInForce::GTC
    ) {
        $this->validateQuantity($quantity);
        $this->validatePrices($type, $price, $stopPrice);

        $this->id = $id;
        $this->userId = $userId;
        $this->symbol = strtoupper($symbol);
        $this->type = $type;
        $this->side = $side;
        $this->status = OrderStatus::PENDING;
        $this->quantity = $quantity;
        $this->price = $price;
        $this->stopPrice = $stopPrice;
        $this->timeInForce = $timeInForce;
        $this->executedQuantity = 0.0;
        $this->cumulativeQuoteQuantity = 0.0;
        $this->exchangeOrderId = null;
        $this->clientOrderId = null;
        $this->executedAt = null;
        $this->rejectReason = null;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public static function create(
        UserId $userId,
        string $symbol,
        OrderType $type,
        OrderSide $side,
        float $quantity,
        ?float $price = null,
        ?float $stopPrice = null,
        TimeInForce $timeInForce = TimeInForce::GTC
    ): self {
        return new self(
            OrderId::generate(),
            $userId,
            $symbol,
            $type,
            $side,
            $quantity,
            $price,
            $stopPrice,
            $timeInForce
        );
    }

    public function submit(string $exchangeOrderId, string $clientOrderId): void
    {
        if ($this->status !== OrderStatus::PENDING) {
            throw new \DomainException('Only pending orders can be submitted');
        }

        $this->exchangeOrderId = $exchangeOrderId;
        $this->clientOrderId = $clientOrderId;
        $this->status = OrderStatus::NEW;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function updateStatus(
        OrderStatus $status,
        float $executedQuantity = null,
        float $cumulativeQuoteQuantity = null
    ): void {
        $this->status = $status;
        
        if ($executedQuantity !== null) {
            $this->executedQuantity = $executedQuantity;
        }
        
        if ($cumulativeQuoteQuantity !== null) {
            $this->cumulativeQuoteQuantity = $cumulativeQuoteQuantity;
        }

        if ($status === OrderStatus::FILLED && $this->executedAt === null) {
            $this->executedAt = new \DateTimeImmutable();
        }

        $this->updatedAt = new \DateTimeImmutable();
    }

    public function cancel(): void
    {
        if (!$this->status->canBeCancelled()) {
            throw new \DomainException('Order cannot be cancelled in current status');
        }

        $this->status = OrderStatus::CANCELLED;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function reject(string $reason): void
    {
        $this->status = OrderStatus::REJECTED;
        $this->rejectReason = $reason;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getFilledPercentage(): float
    {
        if ($this->quantity === 0.0) {
            return 0.0;
        }

        return ($this->executedQuantity / $this->quantity) * 100;
    }

    public function getRemainingQuantity(): float
    {
        return max(0.0, $this->quantity - $this->executedQuantity);
    }

    public function getAveragePrice(): ?float
    {
        if ($this->executedQuantity === 0.0) {
            return null;
        }

        return $this->cumulativeQuoteQuantity / $this->executedQuantity;
    }

    public function getTotalValue(): float
    {
        if ($this->type === OrderType::MARKET) {
            return $this->cumulativeQuoteQuantity;
        }

        if ($this->price !== null) {
            return $this->quantity * $this->price;
        }

        return 0.0;
    }

    private function validateQuantity(float $quantity): void
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be greater than 0');
        }
    }

    private function validatePrices(OrderType $type, ?float $price, ?float $stopPrice): void
    {
        if ($type->requiresPrice() && $price === null) {
            throw new \InvalidArgumentException(
                sprintf('Order type %s requires a price', $type->value)
            );
        }

        if ($type->requiresStopPrice() && $stopPrice === null) {
            throw new \InvalidArgumentException(
                sprintf('Order type %s requires a stop price', $type->value)
            );
        }

        if ($price !== null && $price <= 0) {
            throw new \InvalidArgumentException('Price must be greater than 0');
        }

        if ($stopPrice !== null && $stopPrice <= 0) {
            throw new \InvalidArgumentException('Stop price must be greater than 0');
        }
    }

    // Getters
    public function getId(): OrderId
    {
        return $this->id;
    }

    public function getUserId(): UserId
    {
        return $this->userId;
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    public function getType(): OrderType
    {
        return $this->type;
    }

    public function getSide(): OrderSide
    {
        return $this->side;
    }

    public function getStatus(): OrderStatus
    {
        return $this->status;
    }

    public function getQuantity(): float
    {
        return $this->quantity;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function getStopPrice(): ?float
    {
        return $this->stopPrice;
    }

    public function getTimeInForce(): TimeInForce
    {
        return $this->timeInForce;
    }

    public function getExecutedQuantity(): float
    {
        return $this->executedQuantity;
    }

    public function getCumulativeQuoteQuantity(): float
    {
        return $this->cumulativeQuoteQuantity;
    }

    public function getExchangeOrderId(): ?string
    {
        return $this->exchangeOrderId;
    }

    public function getClientOrderId(): ?string
    {
        return $this->clientOrderId;
    }

    public function getExecutedAt(): ?\DateTimeImmutable
    {
        return $this->executedAt;
    }

    public function getRejectReason(): ?string
    {
        return $this->rejectReason;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
