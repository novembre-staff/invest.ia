<?php

declare(strict_types=1);

namespace App\Trading\Application\Handler;

use App\Exchange\Domain\Repository\ExchangeConnectionRepositoryInterface;
use App\Exchange\Domain\ValueObject\ExchangeConnectionId;
use App\Identity\Domain\ValueObject\UserId;
use App\Trading\Application\Command\CancelOrder;
use App\Trading\Domain\Event\OrderCancelled;
use App\Trading\Domain\Repository\OrderRepositoryInterface;
use App\Trading\Domain\ValueObject\OrderId;
use App\Trading\Infrastructure\Adapter\OrderExecutorInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class CancelOrderHandler
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private ExchangeConnectionRepositoryInterface $exchangeConnectionRepository,
        private OrderExecutorInterface $orderExecutor,
        private MessageBusInterface $eventBus
    ) {
    }

    public function __invoke(CancelOrder $command): void
    {
        $orderId = OrderId::fromString($command->orderId);
        $userId = UserId::fromString($command->userId);

        $order = $this->orderRepository->findById($orderId);

        if ($order === null) {
            throw new \DomainException('Order not found');
        }

        // Verify ownership
        if (!$order->getUserId()->equals($userId)) {
            throw new \DomainException('You do not have permission to cancel this order');
        }

        // Load exchange connection
        $connectionId = ExchangeConnectionId::fromString($command->exchangeConnectionId);
        $connection = $this->exchangeConnectionRepository->findById($connectionId);

        if ($connection === null) {
            throw new \DomainException('Exchange connection not found');
        }

        try {
            // Cancel on exchange
            $credentials = $connection->getCredentials();
            $this->orderExecutor->cancel(
                $order,
                $credentials->getApiKey(),
                $credentials->getDecryptedApiSecret()
            );

            // Update order status
            $order->cancel();
            $this->orderRepository->save($order);

            // Dispatch event
            $this->eventBus->dispatch(
                OrderCancelled::now(
                    $order->getId(),
                    $userId->getValue(),
                    $order->getSymbol()
                )
            );
        } catch (\Exception $e) {
            throw new \DomainException('Failed to cancel order: ' . $e->getMessage(), 0, $e);
        }
    }
}
