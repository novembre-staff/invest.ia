<?php

declare(strict_types=1);

namespace App\Exchange\Application\Handler;

use App\Exchange\Application\Command\DisconnectExchange;
use App\Exchange\Domain\Event\ExchangeDisconnected;
use App\Exchange\Domain\Repository\ExchangeConnectionRepositoryInterface;
use App\Exchange\Domain\ValueObject\ExchangeConnectionId;
use App\Identity\Domain\ValueObject\UserId;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class DisconnectExchangeHandler
{
    public function __construct(
        private ExchangeConnectionRepositoryInterface $connectionRepository,
        private MessageBusInterface $eventBus
    ) {
    }

    public function __invoke(DisconnectExchange $command): void
    {
        $connectionId = ExchangeConnectionId::fromString($command->connectionId);
        $userId = UserId::fromString($command->userId);

        $connection = $this->connectionRepository->findById($connectionId);

        if ($connection === null) {
            throw new \DomainException('Exchange connection not found');
        }

        // Verify ownership
        if (!$connection->getUserId()->equals($userId)) {
            throw new \DomainException('Unauthorized to disconnect this exchange');
        }

        // Deactivate instead of delete to preserve history
        $connection->deactivate();
        $this->connectionRepository->save($connection);

        // Dispatch event
        $this->eventBus->dispatch(
            ExchangeDisconnected::now(
                $connection->getId(),
                $userId,
                $connection->getExchangeName()
            )
        );
    }
}
