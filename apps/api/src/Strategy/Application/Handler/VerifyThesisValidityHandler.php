<?php

declare(strict_types=1);

namespace App\Strategy\Application\Handler;

use App\Strategy\Application\Command\VerifyThesisValidity;
use App\Strategy\Domain\Event\ThesisInvalidated;
use App\Strategy\Domain\Repository\TradingStrategyRepositoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class VerifyThesisValidityHandler
{
    public function __construct(
        private TradingStrategyRepositoryInterface $strategyRepository,
        private MessageBusInterface $eventBus,
        private LoggerInterface $logger
    ) {
    }

    public function __invoke(VerifyThesisValidity $command): bool
    {
        $bot = $this->strategyRepository->findById($command->botId);

        if ($bot === null) {
            throw new \DomainException('Bot not found');
        }

        // TODO: Implement actual thesis verification logic
        // This would compare:
        // 1. Original entry conditions vs current market
        // 2. Expected price movement vs actual
        // 3. Time horizon vs elapsed time
        // 4. Technical indicators alignment
        
        $isValid = $this->evaluateThesis($bot, $command->currentMarketData);

        if (!$isValid) {
            $this->logger->warning('Thesis invalidated for position', [
                'bot_id' => $command->botId->toString(),
                'position_id' => $command->positionId
            ]);

            $event = ThesisInvalidated::now(
                botId: $command->botId,
                userId: $bot->getUserId()->toString(),
                positionId: $command->positionId,
                reasons: $this->getInvalidationReasons($bot, $command->currentMarketData)
            );

            $this->eventBus->dispatch($event);
        }

        return $isValid;
    }

    private function evaluateThesis($bot, array $marketData): bool
    {
        // Simplified thesis validation
        // In production, this would be much more sophisticated
        
        // Check if market conditions drastically changed
        // For now, return true (thesis still valid)
        return true;
    }

    private function getInvalidationReasons($bot, array $marketData): array
    {
        return [
            'Market conditions changed significantly',
            'Technical indicators no longer aligned',
            'Time horizon exceeded without target reached'
        ];
    }
}
