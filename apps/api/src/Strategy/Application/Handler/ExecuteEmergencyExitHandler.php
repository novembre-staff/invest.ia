<?php

declare(strict_types=1);

namespace App\Strategy\Application\Handler;

use App\Strategy\Application\Command\ExecuteEmergencyExit;
use App\Strategy\Domain\Event\EmergencyExitExecuted;
use App\Strategy\Domain\Repository\TradingStrategyRepositoryInterface;
use App\Trading\Application\Command\PlaceOrder;
use App\Trading\Domain\ValueObject\OrderSide;
use App\Trading\Domain\ValueObject\OrderType;
use App\Alert\Application\Command\SendNotification;
use App\Alert\Domain\ValueObject\NotificationChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ExecuteEmergencyExitHandler
{
    public function __construct(
        private TradingStrategyRepositoryInterface $strategyRepository,
        private MessageBusInterface $commandBus,
        private MessageBusInterface $eventBus,
        private LoggerInterface $logger
    ) {
    }

    public function __invoke(ExecuteEmergencyExit $command): void
    {
        $bot = $this->strategyRepository->findById($command->botId);

        if ($bot === null) {
            throw new \DomainException('Bot not found');
        }

        $this->logger->critical('🚨 EMERGENCY EXIT TRIGGERED', [
            'bot_id' => $command->botId->toString(),
            'position_id' => $command->positionId,
            'reason' => $command->reason,
            'conditions' => $command->triggerConditions
        ]);

        // TODO: Get position details and create immediate market sell order
        // For now, we dispatch the event

        $event = EmergencyExitExecuted::now(
            botId: $command->botId,
            userId: $bot->getUserId()->toString(),
            positionId: $command->positionId,
            reason: $command->reason,
            triggerConditions: $command->triggerConditions
        );

        $this->eventBus->dispatch($event);

        // Send URGENT notification to user (all channels)
        $this->commandBus->dispatch(new SendNotification(
            userId: $bot->getUserId()->toString(),
            channels: [
                NotificationChannel::EMAIL,
                NotificationChannel::PUSH,
                NotificationChannel::SMS
            ],
            title: '🚨 EMERGENCY EXIT EXECUTED',
            message: sprintf(
                'Bot "%s" executed emergency exit on position. Reason: %s',
                $bot->getName(),
                $command->reason
            ),
            metadata: [
                'bot_id' => $command->botId->toString(),
                'position_id' => $command->positionId,
                'reason' => $command->reason,
                'conditions' => $command->triggerConditions,
                'severity' => 'critical'
            ]
        ));

        $this->logger->critical('Emergency exit executed', [
            'bot_id' => $command->botId->toString(),
            'position_id' => $command->positionId
        ]);
    }
}
