<?php

declare(strict_types=1);

namespace App\Trading\Application\Handler;

use App\Exchange\Domain\Repository\ExchangeConnectionRepositoryInterface;
use App\Exchange\Domain\ValueObject\ExchangeConnectionId;
use App\Identity\Domain\ValueObject\UserId;
use App\Trading\Application\Command\PlaceOrder;
use App\Trading\Application\DTO\OrderDTO;
use App\Trading\Domain\Event\OrderPlaced;
use App\Trading\Domain\Model\Order;
use App\Trading\Domain\Repository\OrderRepositoryInterface;
use App\Trading\Domain\Service\OrderValidatorInterface;
use App\Trading\Domain\ValueObject\OrderSide;
use App\Trading\Domain\ValueObject\OrderType;
use App\Trading\Domain\ValueObject\TimeInForce;
use App\Trading\Infrastructure\Adapter\OrderExecutorInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class PlaceOrderHandler
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private ExchangeConnectionRepositoryInterface $exchangeConnectionRepository,
        private OrderExecutorInterface $orderExecutor,
        private OrderValidatorInterface $orderValidator,
        private MessageBusInterface $eventBus
    ) {
    }

    public function __invoke(PlaceOrder $command): OrderDTO
    {
        $userId = UserId::fromString($command->userId);
        
        // Load exchange connection for credentials
        $connectionId = ExchangeConnectionId::fromString($command->exchangeConnectionId);
        $connection = $this->exchangeConnectionRepository->findById($connectionId);

        if ($connection === null) {
            throw new \DomainException('Exchange connection not found');
        }

        if (!$connection->getUserId()->equals($userId)) {
            throw new \DomainException('Exchange connection does not belong to user');
        }

        if (!$connection->isActive()) {
            throw new \DomainException('Exchange connection is not active');
        }

        // Create order
        $order = Order::create(
            $userId,
            $command->symbol,
            OrderType::from($command->type),
            OrderSide::from($command->side),
            $command->quantity,
            $command->price,
            $command->stopPrice,
            TimeInForce::from($command->timeInForce)
        );

        // Validate order
        $this->orderValidator->validate($order);

        // Save order (PENDING status)
        $this->orderRepository->save($order);

        try {
            // Execute on exchange
            $credentials = $connection->getCredentials();
            $response = $this->orderExecutor->execute(
                $order,
                $credentials->getApiKey(),
                $credentials->getDecryptedApiSecret()
            );

            // Update order with exchange data
            $order->submit(
                (string)$response['orderId'],
                (string)$response['clientOrderId']
            );

            // Update execution data if available
            if (isset($response['executedQty'], $response['cummulativeQuoteQty'])) {
                $order->updateStatus(
                    \App\Trading\Domain\ValueObject\OrderStatus::fromBinanceStatus($response['status']),
                    (float)$response['executedQty'],
                    (float)$response['cummulativeQuoteQty']
                );
            }

            $this->orderRepository->save($order);

            // Dispatch event
            $this->eventBus->dispatch(
                OrderPlaced::now(
                    $order->getId(),
                    $userId->getValue(),
                    $order->getSymbol(),
                    $order->getType(),
                    $order->getSide(),
                    $order->getQuantity(),
                    $order->getPrice()
                )
            );

            return OrderDTO::fromDomain($order);
        } catch (\Exception $e) {
            // Mark order as rejected
            $order->reject($e->getMessage());
            $this->orderRepository->save($order);
            
            throw new \DomainException('Failed to place order: ' . $e->getMessage(), 0, $e);
        }
    }
}
