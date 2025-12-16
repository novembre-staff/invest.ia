<?php

declare(strict_types=1);

namespace App\Exchange\Application\Handler;

use App\Exchange\Application\Command\ConnectExchange;
use App\Exchange\Application\DTO\ExchangeConnectionDTO;
use App\Exchange\Domain\Event\ExchangeConnected;
use App\Exchange\Domain\Model\ExchangeConnection;
use App\Exchange\Domain\Repository\ExchangeConnectionRepositoryInterface;
use App\Exchange\Domain\Service\EncryptionServiceInterface;
use App\Exchange\Domain\ValueObject\ApiCredentials;
use App\Exchange\Domain\ValueObject\ExchangeConnectionId;
use App\Exchange\Infrastructure\Adapter\ExchangeApiClientInterface;
use App\Identity\Domain\ValueObject\UserId;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class ConnectExchangeHandler
{
    public function __construct(
        private ExchangeConnectionRepositoryInterface $connectionRepository,
        private EncryptionServiceInterface $encryptionService,
        private ExchangeApiClientInterface $apiClient,
        private MessageBusInterface $eventBus
    ) {
    }

    public function __invoke(ConnectExchange $command): ExchangeConnectionDTO
    {
        $userId = UserId::fromString($command->userId);

        // Check if already connected to this exchange
        $existingConnection = $this->connectionRepository->findByUserIdAndExchangeName(
            $userId,
            $command->exchangeName
        );

        if ($existingConnection !== null) {
            throw new \DomainException('Already connected to this exchange');
        }

        // Validate API credentials by testing connection
        $isValid = $this->apiClient->validateCredentials(
            $command->exchangeName,
            $command->apiKey,
            $command->apiSecret
        );

        if (!$isValid) {
            throw new \DomainException('Invalid API credentials');
        }

        // Encrypt credentials
        $encryptedApiKey = $this->encryptionService->encrypt($command->apiKey);
        $encryptedApiSecret = $this->encryptionService->encrypt($command->apiSecret);

        $credentials = ApiCredentials::encrypted($encryptedApiKey, $encryptedApiSecret);

        // Create connection
        $connection = new ExchangeConnection(
            id: ExchangeConnectionId::generate(),
            userId: $userId,
            exchangeName: $command->exchangeName,
            credentials: $credentials,
            label: $command->label
        );

        $this->connectionRepository->save($connection);

        // Dispatch event
        $this->eventBus->dispatch(
            ExchangeConnected::now(
                $connection->getId(),
                $userId,
                $command->exchangeName
            )
        );

        return ExchangeConnectionDTO::fromAggregate($connection);
    }
}
