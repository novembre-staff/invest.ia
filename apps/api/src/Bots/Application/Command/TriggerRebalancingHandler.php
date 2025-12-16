<?php

declare(strict_types=1);

namespace App\Bots\Application\Command;

use App\Bots\Domain\Repository\BotRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Déclenche le rebalancing d'un portfolio de bot
 */
final class TriggerRebalancingHandler
{
    public function __construct(
        private readonly BotRepositoryInterface $botRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    public function __invoke(TriggerRebalancing $command): void
    {
        $bot = $this->botRepository->findById($command->botId());

        if (!$bot) {
            throw new \RuntimeException(
                sprintf('Bot %s not found', $command->botId())
            );
        }

        $this->logger->info('Rebalancing triggered', [
            'bot_id' => $command->botId(),
            'reason' => $command->reason()
        ]);

        // TODO: Implémenter logique de rebalancing
        // 1. Calculer allocation cible
        // 2. Calculer allocation actuelle
        // 3. Déterminer trades nécessaires
        // 4. Créer propositions ou exécuter auto selon config
    }
}
