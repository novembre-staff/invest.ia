<?php

declare(strict_types=1);

namespace App\Strategy\Application\Handler;

use App\Strategy\Application\Command\ApproveExitAction;
use App\Strategy\Domain\Event\ExitActionApproved;
use App\Strategy\Domain\Repository\TradingStrategyRepositoryInterface;
use App\Trading\Application\Command\PlaceOrder;
use App\Trading\Domain\ValueObject\OrderSide;
use App\Trading\Domain\ValueObject\OrderType;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ApproveExitActionHandler
{
    public function __construct(
        private TradingStrategyRepositoryInterface $strategyRepository,
        private MessageBusInterface $commandBus,
        private MessageBusInterface $eventBus,
        private LoggerInterface $logger
    ) {
    }

    public function __invoke(ApproveExitAction $command): void
    {
        $bot = $this->strategyRepository->findById($command->botId);

        if ($bot === null) {
            throw new \DomainException('Bot not found');
        }

        if ($bot->getUserId()->toString() !== $command->userId) {
            throw new \DomainException('Unauthorized to approve this action');
        }

        $this->logger->info('Exit action approved by user', [
            'bot_id' => $command->botId->toString(),
            'position_id' => $command->positionId,
            'user_id' => $command->userId
        ]);

        // TODO: Get position details to create exit order
        // For now, we dispatch the event
        
        $event = ExitActionApproved::now(
            botId: $command->botId,
            userId: $command->userId,
            positionId: $command->positionId,
            proposalId: $command->proposalId
        );

        $this->eventBus->dispatch($event);

        $this->logger->info('Exit action approved event dispatched', [
            'bot_id' => $command->botId->toString(),
            'position_id' => $command->positionId
        ]);
    }
}
