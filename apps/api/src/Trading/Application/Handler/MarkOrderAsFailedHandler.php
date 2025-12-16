<?php

declare(strict_types=1);

namespace App\Trading\Application\Handler;

use App\Trading\Application\Command\MarkOrderAsFailed;
use App\Trading\Domain\Event\OrderFailed;
use App\Trading\Domain\Repository\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class MarkOrderAsFailedHandler
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private MessageBusInterface $eventBus,
        private LoggerInterface $logger
    ) {
    }

    public function __invoke(MarkOrderAsFailed $command): void
    {
        $order = $this->orderRepository->findById($command->orderId);

        if ($order === null) {
            throw new \DomainException('Order not found');
        }

        // Reject order with reason
        $order->reject($command->errorMessage);
        $this->orderRepository->save($order);

        // Dispatch event
        $event = OrderFailed::now(
            orderId: $order->getId(),
            symbol: $order->getSymbol(),
            userId: $order->getUserId()->toString(),
            errorCode: $command->errorCode,
            errorMessage: $command->errorMessage
        );

        $this->eventBus->dispatch($event);

        $this->logger->error('Order failed', [
            'order_id' => $command->orderId->toString(),
            'error_code' => $command->errorCode,
            'error_message' => $command->errorMessage,
            'symbol' => $order->getSymbol()
        ]);
    }
}
