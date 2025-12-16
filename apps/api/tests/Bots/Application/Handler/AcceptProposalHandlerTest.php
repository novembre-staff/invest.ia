<?php

declare(strict_types=1);

namespace App\Tests\Bots\Application\Handler;

use App\Bots\Application\Command\AcceptProposal;
use App\Bots\Application\Handler\AcceptProposalHandler;
use App\Bots\Domain\Event\ProposalAccepted;
use App\Bots\Domain\Model\Proposal;
use App\Bots\Domain\Repository\ProposalRepositoryInterface;
use App\Bots\Domain\ValueObject\ProposalId;
use App\Identity\Domain\ValueObject\UserId;
use App\Strategy\Domain\ValueObject\StrategyId;
use App\Trading\Application\Command\PlaceOrder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

class AcceptProposalHandlerTest extends TestCase
{
    private ProposalRepositoryInterface $proposalRepository;
    private MessageBusInterface $commandBus;
    private MessageBusInterface $eventBus;
    private AcceptProposalHandler $handler;
    
    protected function setUp(): void
    {
        $this->proposalRepository = $this->createMock(ProposalRepositoryInterface::class);
        $this->commandBus = $this->createMock(MessageBusInterface::class);
        $this->eventBus = $this->createMock(MessageBusInterface::class);
        
        $this->handler = new AcceptProposalHandler(
            $this->proposalRepository,
            $this->commandBus,
            $this->eventBus
        );
    }
    
    public function testAcceptProposalSuccess(): void
    {
        // Arrange
        $proposalId = ProposalId::generate();
        $userId = UserId::generate();
        $strategyId = StrategyId::generate();
        
        $proposal = new Proposal(
            $proposalId,
            $userId,
            $strategyId,
            'BTCUSDT',
            'buy',
            '0.1',
            'Strong momentum',
            ['volatility' => 'medium'],
            'MEDIUM',
            '50000.00'
        );
        
        $command = new AcceptProposal(
            proposalId: $proposalId->getValue(),
            userId: $userId->getValue()
        );
        
        $this->proposalRepository
            ->expects($this->once())
            ->method('findById')
            ->with($proposalId)
            ->willReturn($proposal);
        
        $this->proposalRepository
            ->expects($this->exactly(2))
            ->method('save')
            ->with($proposal);
        
        $this->eventBus
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(ProposalAccepted::class))
            ->willReturn(new Envelope(new \stdClass()));
        
        // Mock PlaceOrder command dispatch
        $orderResult = ['orderId' => 'order-123'];
        $envelope = new Envelope(new PlaceOrder('', '', '', '', '', '', null));
        $envelope = $envelope->with(new HandledStamp($orderResult, 'handler'));
        
        $this->commandBus
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(PlaceOrder::class))
            ->willReturn($envelope);
        
        // Act
        $result = ($this->handler)($command);
        
        // Assert
        $this->assertEquals('order-123', $result['orderId']);
        $this->assertEquals($proposalId->getValue(), $result['proposalId']);
    }
    
    public function testAcceptProposalNotFound(): void
    {
        // Arrange
        $command = new AcceptProposal(
            proposalId: ProposalId::generate()->getValue(),
            userId: UserId::generate()->getValue()
        );
        
        $this->proposalRepository
            ->expects($this->once())
            ->method('findById')
            ->willReturn(null);
        
        // Assert
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Proposal not found');
        
        // Act
        ($this->handler)($command);
    }
    
    public function testAcceptProposalUnauthorized(): void
    {
        // Arrange
        $proposalId = ProposalId::generate();
        $proposalUserId = UserId::generate();
        $differentUserId = UserId::generate();
        
        $proposal = new Proposal(
            $proposalId,
            $proposalUserId,
            StrategyId::generate(),
            'BTCUSDT',
            'buy',
            '0.1',
            'Test',
            [],
            'LOW'
        );
        
        $command = new AcceptProposal(
            proposalId: $proposalId->getValue(),
            userId: $differentUserId->getValue()
        );
        
        $this->proposalRepository
            ->expects($this->once())
            ->method('findById')
            ->willReturn($proposal);
        
        // Assert
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Unauthorized');
        
        // Act
        ($this->handler)($command);
    }
    
    public function testAcceptExpiredProposal(): void
    {
        // Arrange
        $proposalId = ProposalId::generate();
        $userId = UserId::generate();
        
        $proposal = new Proposal(
            $proposalId,
            $userId,
            StrategyId::generate(),
            'BTCUSDT',
            'buy',
            '0.1',
            'Test',
            [],
            'LOW',
            null,
            null,
            null,
            null,
            0 // Expire immédiatement
        );
        
        $command = new AcceptProposal(
            proposalId: $proposalId->getValue(),
            userId: $userId->getValue()
        );
        
        $this->proposalRepository
            ->expects($this->once())
            ->method('findById')
            ->willReturn($proposal);
        
        // Should save with expired status
        $this->proposalRepository
            ->expects($this->once())
            ->method('save');
        
        // Assert
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Proposal has expired');
        
        // Act
        ($this->handler)($command);
    }
}
