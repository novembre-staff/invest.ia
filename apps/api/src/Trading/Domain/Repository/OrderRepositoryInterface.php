<?php

declare(strict_types=1);

namespace App\Trading\Domain\Repository;

use App\Identity\Domain\ValueObject\UserId;
use App\Trading\Domain\Model\Order;
use App\Trading\Domain\ValueObject\OrderId;
use App\Trading\Domain\ValueObject\OrderStatus;

interface OrderRepositoryInterface
{
    public function save(Order $order): void;

    public function findById(OrderId $id): ?Order;

    public function findByExchangeOrderId(string $exchangeOrderId): ?Order;

    /**
     * @return Order[]
     */
    public function findByUserId(UserId $userId, ?int $limit = null): array;

    /**
     * @return Order[]
     */
    public function findActiveByUserId(UserId $userId): array;

    /**
     * @return Order[]
     */
    public function findByUserIdAndSymbol(UserId $userId, string $symbol, ?int $limit = null): array;

    /**
     * @return Order[]
     */
    public function findByStatus(OrderStatus $status): array;

    public function delete(Order $order): void;
}
