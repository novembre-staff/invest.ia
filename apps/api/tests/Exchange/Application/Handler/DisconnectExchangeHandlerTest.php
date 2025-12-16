<?php

declare(strict_types=1);

namespace App\Tests\Exchange\Application\Handler;

use App\Exchange\Application\Command\DisconnectExchange;
use App\Exchange\Application\Handler\DisconnectExchangeHandler;
use App\Exchange\Domain\Event\ExchangeDisconnected;
use App\Exchange\Domain\Model\ExchangeConnection;
use App\Exchange\Domain\Repository\ExchangeConnectionRepositoryInterface;
use App\Exchange\Domain\ValueObject\ApiCredentials;
use App\Exchange\Domain\ValueObject\ExchangeConnectionId;
use App\Identity\Domain\ValueObject\UserId;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class DisconnectExchangeHandlerTest extends TestCase
{
    private ExchangeConnectionRepositoryInterface $connectionRepository;
    private MessageBusInterface $eventBus;
    private DisconnectExchangeHandler $handler;

    protected function setUp(): void
    {
        $this->connectionRepository = $this->createMock(ExchangeConnectionRepositoryInterface::class);
        $this->eventBus = $this->createMock(MessageBusInterface::class);

        $this->handler = new DisconnectExchangeHandler(
            $this->connectionRepository,
            $this->eventBus
        );
    }

    public function testDisconnectExchangeSuccess(): void
    {
        // Arrange
        $connectionId = ExchangeConnectionId::generate();
        $userId = UserId::generate();

        $connection = new ExchangeConnection(
            id: $connectionId,
            userId: $userId,
            exchangeName: 'binance',
            credentials: ApiCredentials::encrypted('encrypted-key', 'encrypted-secret')
        );

        $command = new DisconnectExchange(
            connectionId: $connectionId->getValue(),
            userId: $userId->getValue()
        );

        $this->connectionRepository
            ->expects($this->once())
            ->method('findById')
            ->with($connectionId)
            ->willReturn($connection);

        $this->connectionRepository
            ->expects($this->once())
            ->method('save')
            ->with($connection);

        $this->eventBus
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(ExchangeDisconnected::class))
            ->willReturn(new Envelope(new \stdClass()));

        // Act
        ($this->handler)($command);

        // Assert
        $this->assertFalse($connection->isActive());
    }

    public function testDisconnectExchangeNotFound(): void
    {
        // Arrange
        $connectionId = ExchangeConnectionId::generate();
        $userId = UserId::generate();

        $command = new DisconnectExchange(
            connectionId: $connectionId->getValue(),
            userId: $userId->getValue()
        );

        $this->connectionRepository
            ->expects($this->once())
            ->method('findById')
            ->willReturn(null);

        // Assert
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Exchange connection not found');

        // Act
        ($this->handler)($command);
    }

    public function testDisconnectExchangeUnauthorized(): void
    {
        // Arrange
        $connectionId = ExchangeConnectionId::generate();
        $ownerId = UserId::generate();
        $differentUserId = UserId::generate();

        $connection = new ExchangeConnection(
            id: $connectionId,
            userId: $ownerId,
            exchangeName: 'binance',
            credentials: ApiCredentials::encrypted('encrypted-key', 'encrypted-secret')
        );

        $command = new DisconnectExchange(
            connectionId: $connectionId->getValue(),
            userId: $differentUserId->getValue()
        );

        $this->connectionRepository
            ->expects($this->once())
            ->method('findById')
            ->willReturn($connection);

        // Assert
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Unauthorized to disconnect this exchange');

        // Act
        ($this->handler)($command);
    }
}
