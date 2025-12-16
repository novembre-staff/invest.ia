<?php

declare(strict_types=1);

namespace App\Risk\Application\Handler;

use App\Risk\Application\Command\ActivateBotKillSwitch;
use App\Risk\Domain\Event\BotKillSwitchActivated;
use App\Strategy\Domain\Repository\TradingStrategyRepositoryInterface;
use App\Trading\Application\Command\CancelOrder;
use App\Trading\Domain\Repository\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ActivateBotKillSwitchHandler
{
    public function __construct(
        private TradingStrategyRepositoryInterface $strategyRepository,
        private OrderRepositoryInterface $orderRepository,
        private MessageBusInterface $commandBus,
        private MessageBusInterface $eventBus,
        private LoggerInterface $logger
    ) {
    }

    public function __invoke(ActivateBotKillSwitch $command): void
    {
        $bot = $this->strategyRepository->findById($command->botId);

        if ($bot === null) {
            throw new \DomainException('Bot not found');
        }

        if ($bot->getUserId()->toString() !== $command->userId) {
            throw new \DomainException('Unauthorized to stop this bot');
        }

        $this->logger->warning('🛑 Bot kill switch activated', [
            'bot_id' => $command->botId->toString(),
            'user_id' => $command->userId,
            'reason' => $command->reason
        ]);

        // 1. Pause the bot
        $bot->pause();
        $this->strategyRepository->save($bot);

        // 2. Cancel all active orders for this bot
        // TODO: Add method to find orders by strategy/bot ID
        // For now, we log this as a limitation
        $this->logger->info('Note: Order cancellation by bot ID requires additional implementation');

        // 3. Dispatch event
        $event = BotKillSwitchActivated::now(
            botId: $command->botId,
            userId: $command->userId,
            reason: $command->reason
        );

        $this->eventBus->dispatch($event);

        $this->logger->info('Bot kill switch completed', [
            'bot_id' => $command->botId->toString()
        ]);
    }
}
