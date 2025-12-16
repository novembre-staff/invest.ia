<?php

declare(strict_types=1);

namespace App\Trading\Application\Handler;

use App\Identity\Domain\ValueObject\UserId;
use App\Trading\Application\DTO\OrderDTO;
use App\Trading\Application\Query\GetUserOrders;
use App\Trading\Domain\Repository\OrderRepositoryInterface;

final readonly class GetUserOrdersHandler
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository
    ) {
    }

    /**
     * @return OrderDTO[]
     */
    public function __invoke(GetUserOrders $query): array
    {
        $userId = UserId::fromString($query->userId);

        if ($query->activeOnly) {
            $orders = $this->orderRepository->findActiveByUserId($userId);
        } elseif ($query->symbol !== null) {
            $orders = $this->orderRepository->findByUserIdAndSymbol(
                $userId,
                $query->symbol,
                $query->limit
            );
        } else {
            $orders = $this->orderRepository->findByUserId($userId, $query->limit);
        }

        return array_map(
            fn($order) => OrderDTO::fromDomain($order),
            $orders
        );
    }
}
