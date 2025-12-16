<?php

declare(strict_types=1);

namespace App\Risk\Application\Handler;

use App\Risk\Application\Command\ActivateGlobalKillSwitch;
use App\Risk\Domain\Event\GlobalKillSwitchActivated;
use App\Strategy\Domain\Repository\TradingStrategyRepositoryInterface;
use App\Trading\Application\Command\CancelOrder;
use App\Trading\Domain\Repository\OrderRepositoryInterface;
use App\Trading\Domain\ValueObject\OrderStatus;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ActivateGlobalKillSwitchHandler
{
    public function __construct(
        private TradingStrategyRepositoryInterface $strategyRepository,
        private OrderRepositoryInterface $orderRepository,
        private MessageBusInterface $commandBus,
        private MessageBusInterface $eventBus,
        private LoggerInterface $logger
    ) {
    }

    public function __invoke(ActivateGlobalKillSwitch $command): void
    {
        $this->logger->critical('🚨 GLOBAL KILL SWITCH ACTIVATED', [
            'user_id' => $command->userId,
            'reason' => $command->reason,
            'timestamp' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM)
        ]);

        // 1. Stop all active bots
        $activeBots = $this->strategyRepository->findActiveStrategies();
        $stoppedBots = 0;
        
        foreach ($activeBots as $bot) {
            try {
                $bot->pause();
                $this->strategyRepository->save($bot);
                $stoppedBots++;
            } catch (\Exception $e) {
                $this->logger->error('Failed to stop bot during kill switch', [
                    'bot_id' => $bot->getId()->toString(),
                    'error' => $e->getMessage()
                ]);
            }
        }

        // 2. Cancel all active orders
        $activeOrders = $this->orderRepository->findByStatus(OrderStatus::NEW);
        $partiallyFilledOrders = $this->orderRepository->findByStatus(OrderStatus::PARTIALLY_FILLED);
        $allActiveOrders = array_merge($activeOrders, $partiallyFilledOrders);
        $cancelledOrders = 0;

        foreach ($allActiveOrders as $order) {
            try {
                $this->commandBus->dispatch(new CancelOrder(
                    orderId: $order->getId(),
                    userId: $order->getUserId()->toString(),
                    reason: "Global kill switch activated: {$command->reason}"
                ));
                $cancelledOrders++;
            } catch (\Exception $e) {
                $this->logger->error('Failed to cancel order during kill switch', [
                    'order_id' => $order->getId()->toString(),
                    'error' => $e->getMessage()
                ]);
            }
        }

        // 3. Dispatch event
        $event = GlobalKillSwitchActivated::now(
            userId: $command->userId,
            reason: $command->reason,
            stoppedBots: $stoppedBots,
            cancelledOrders: $cancelledOrders
        );

        $this->eventBus->dispatch($event);

        $this->logger->critical('Global kill switch completed', [
            'stopped_bots' => $stoppedBots,
            'cancelled_orders' => $cancelledOrders
        ]);
    }
}
