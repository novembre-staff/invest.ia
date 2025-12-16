<?php

declare(strict_types=1);

namespace App\Strategy\Application\Handler;

use App\Strategy\Application\Command\ProposeBotAction;
use App\Strategy\Domain\Event\BotActionProposed;
use App\Strategy\Domain\Repository\TradingStrategyRepositoryInterface;
use App\Alert\Application\Command\SendNotification;
use App\Alert\Domain\ValueObject\NotificationChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ProposeBotActionHandler
{
    public function __construct(
        private TradingStrategyRepositoryInterface $strategyRepository,
        private MessageBusInterface $commandBus,
        private MessageBusInterface $eventBus,
        private LoggerInterface $logger
    ) {
    }

    public function __invoke(ProposeBotAction $command): void
    {
        $bot = $this->strategyRepository->findById($command->botId);

        if ($bot === null) {
            throw new \DomainException('Bot not found');
        }

        $this->logger->info('Bot proposing action', [
            'bot_id' => $command->botId->toString(),
            'position_id' => $command->positionId,
            'action_type' => $command->actionType->value,
            'urgent' => $command->urgent
        ]);

        // Dispatch event
        $event = BotActionProposed::now(
            botId: $command->botId,
            userId: $bot->getUserId()->toString(),
            positionId: $command->positionId,
            actionType: $command->actionType,
            reasoning: $command->reasoning,
            marketConditions: $command->marketConditions,
            targetPercentage: $command->targetPercentage,
            urgent: $command->urgent
        );

        $this->eventBus->dispatch($event);

        // Send notification to user
        $channels = $command->urgent 
            ? [NotificationChannel::EMAIL, NotificationChannel::PUSH, NotificationChannel::SMS]
            : [NotificationChannel::EMAIL, NotificationChannel::PUSH];

        $this->commandBus->dispatch(new SendNotification(
            userId: $bot->getUserId()->toString(),
            channels: $channels,
            title: sprintf(
                '%s Bot Action Required: %s',
                $command->urgent ? '🚨' : '💡',
                $command->actionType->getDisplayName()
            ),
            message: sprintf(
                'Bot "%s" proposes to %s position. %s',
                $bot->getName(),
                $command->actionType->value,
                $command->reasoning
            ),
            metadata: [
                'bot_id' => $command->botId->toString(),
                'position_id' => $command->positionId,
                'action_type' => $command->actionType->value,
                'urgent' => $command->urgent
            ]
        ));

        $this->logger->info('Bot action proposal sent', [
            'bot_id' => $command->botId->toString(),
            'action_type' => $command->actionType->value
        ]);
    }
}
