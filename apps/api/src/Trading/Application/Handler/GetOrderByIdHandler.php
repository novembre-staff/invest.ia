<?php

declare(strict_types=1);

namespace App\Trading\Application\Handler;

use App\Identity\Domain\ValueObject\UserId;
use App\Trading\Application\DTO\OrderDTO;
use App\Trading\Application\Query\GetOrderById;
use App\Trading\Domain\Repository\OrderRepositoryInterface;
use App\Trading\Domain\ValueObject\OrderId;

final readonly class GetOrderByIdHandler
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository
    ) {
    }

    public function __invoke(GetOrderById $query): OrderDTO
    {
        $orderId = OrderId::fromString($query->orderId);
        $userId = UserId::fromString($query->userId);

        $order = $this->orderRepository->findById($orderId);

        if ($order === null) {
            throw new \DomainException('Order not found');
        }

        // Verify ownership
        if (!$order->getUserId()->equals($userId)) {
            throw new \DomainException('You do not have permission to view this order');
        }

        return OrderDTO::fromDomain($order);
    }
}
