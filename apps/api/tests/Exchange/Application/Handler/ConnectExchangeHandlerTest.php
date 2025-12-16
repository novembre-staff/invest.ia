<?php

declare(strict_types=1);

namespace App\Tests\Exchange\Application\Handler;

use App\Exchange\Application\Command\ConnectExchange;
use App\Exchange\Application\Handler\ConnectExchangeHandler;
use App\Exchange\Domain\Event\ExchangeConnected;
use App\Exchange\Domain\Model\ExchangeConnection;
use App\Exchange\Domain\Repository\ExchangeConnectionRepositoryInterface;
use App\Exchange\Domain\Service\EncryptionServiceInterface;
use App\Exchange\Infrastructure\Adapter\ExchangeApiClientInterface;
use App\Identity\Domain\ValueObject\UserId;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class ConnectExchangeHandlerTest extends TestCase
{
    private ExchangeConnectionRepositoryInterface $connectionRepository;
    private EncryptionServiceInterface $encryptionService;
    private ExchangeApiClientInterface $apiClient;
    private MessageBusInterface $eventBus;
    private ConnectExchangeHandler $handler;

    protected function setUp(): void
    {
        $this->connectionRepository = $this->createMock(ExchangeConnectionRepositoryInterface::class);
        $this->encryptionService = $this->createMock(EncryptionServiceInterface::class);
        $this->apiClient = $this->createMock(ExchangeApiClientInterface::class);
        $this->eventBus = $this->createMock(MessageBusInterface::class);

        $this->handler = new ConnectExchangeHandler(
            $this->connectionRepository,
            $this->encryptionService,
            $this->apiClient,
            $this->eventBus
        );
    }

    public function testConnectExchangeSuccess(): void
    {
        // Arrange
        $userId = UserId::generate();
        $command = new ConnectExchange(
            userId: $userId->getValue(),
            exchangeName: 'binance',
            apiKey: 'test-api-key',
            apiSecret: 'test-api-secret',
            label: 'My Binance Account'
        );

        $this->connectionRepository
            ->expects($this->once())
            ->method('findByUserIdAndExchangeName')
            ->with($userId, 'binance')
            ->willReturn(null);

        $this->apiClient
            ->expects($this->once())
            ->method('validateCredentials')
            ->with('binance', 'test-api-key', 'test-api-secret')
            ->willReturn(true);

        $this->encryptionService
            ->expects($this->exactly(2))
            ->method('encrypt')
            ->willReturnCallback(fn($value) => "encrypted_{$value}");

        $this->connectionRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(ExchangeConnection::class));

        $this->eventBus
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(ExchangeConnected::class))
            ->willReturn(new Envelope(new \stdClass()));

        // Act
        $result = ($this->handler)($command);

        // Assert
        $this->assertEquals('binance', $result->exchangeName);
        $this->assertEquals('My Binance Account', $result->label);
        $this->assertTrue($result->isActive);
    }

    public function testConnectExchangeAlreadyConnected(): void
    {
        // Arrange
        $userId = UserId::generate();
        $command = new ConnectExchange(
            userId: $userId->getValue(),
            exchangeName: 'binance',
            apiKey: 'test-api-key',
            apiSecret: 'test-api-secret'
        );

        $existingConnection = $this->createMock(ExchangeConnection::class);

        $this->connectionRepository
            ->expects($this->once())
            ->method('findByUserIdAndExchangeName')
            ->willReturn($existingConnection);

        // Assert
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Already connected to this exchange');

        // Act
        ($this->handler)($command);
    }

    public function testConnectExchangeInvalidCredentials(): void
    {
        // Arrange
        $userId = UserId::generate();
        $command = new ConnectExchange(
            userId: $userId->getValue(),
            exchangeName: 'binance',
            apiKey: 'invalid-key',
            apiSecret: 'invalid-secret'
        );

        $this->connectionRepository
            ->expects($this->once())
            ->method('findByUserIdAndExchangeName')
            ->willReturn(null);

        $this->apiClient
            ->expects($this->once())
            ->method('validateCredentials')
            ->willReturn(false);

        // Assert
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Invalid API credentials');

        // Act
        ($this->handler)($command);
    }
}
