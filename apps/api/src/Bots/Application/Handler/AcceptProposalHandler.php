<?php

declare(strict_types=1);

namespace App\Bots\Application\Handler;

use App\Bots\Application\Command\AcceptProposal;
use App\Bots\Domain\Event\ProposalAccepted;
use App\Bots\Domain\Repository\ProposalRepositoryInterface;
use App\Bots\Domain\ValueObject\ProposalId;
use App\Identity\Domain\ValueObject\UserId;
use App\Trading\Application\Command\PlaceOrder;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class AcceptProposalHandler
{
    public function __construct(
        private readonly ProposalRepositoryInterface $proposalRepository,
        private readonly MessageBusInterface $commandBus,
        private readonly MessageBusInterface $eventBus
    ) {}

    /**
     * Accepter une proposition et créer l'ordre correspondant
     * 
     * @return array{orderId: string, proposalId: string}
     */
    public function __invoke(AcceptProposal $command): array
    {
        $proposalId = ProposalId::fromString($command->proposalId);
        $userId = UserId::fromString($command->userId);
        
        $proposal = $this->proposalRepository->findById($proposalId);
        
        if (!$proposal) {
            throw new \DomainException('Proposal not found.');
        }
        
        // Vérifier que la proposition appartient à l'utilisateur
        if (!$proposal->getUserId()->equals($userId)) {
            throw new \DomainException('Unauthorized: Proposal does not belong to this user.');
        }
        
        // Vérifier que la proposition n'est pas expirée
        if ($proposal->isExpired()) {
            $proposal->expire();
            $this->proposalRepository->save($proposal);
            throw new \DomainException('Proposal has expired.');
        }
        
        // Accepter la proposition
        $proposal->accept();
        $this->proposalRepository->save($proposal);
        
        // Dispatch domain event
        $this->eventBus->dispatch(new ProposalAccepted(
            $proposal->getId(),
            $proposal->getUserId(),
            $proposal->getSymbol(),
            $proposal->getSide(),
            $proposal->getQuantity(),
            new \DateTimeImmutable()
        ));
        
        // Créer l'ordre correspondant (UC-034)
        $placeOrderCommand = new PlaceOrder(
            userId: $command->userId,
            strategyId: $proposal->getStrategyId()->getValue(),
            symbol: $proposal->getSymbol(),
            side: $proposal->getSide(),
            quantity: $proposal->getQuantity(),
            orderType: $proposal->getLimitPrice() ? 'limit' : 'market',
            price: $proposal->getLimitPrice(),
            stopLoss: $proposal->getStopLoss(),
            takeProfit: $proposal->getTakeProfit()
        );
        
        // Dispatch la commande PlaceOrder
        $envelope = $this->commandBus->dispatch($placeOrderCommand);
        $handledStamp = $envelope->last(\Symfony\Component\Messenger\Stamp\HandledStamp::class);
        
        if (!$handledStamp) {
            throw new \RuntimeException('PlaceOrder command was not handled');
        }
        
        $orderResult = $handledStamp->getResult();
        $orderId = is_array($orderResult) ? ($orderResult['orderId'] ?? null) : null;
        
        if ($orderId) {
            $proposal->markAsExecuted($orderId);
            $this->proposalRepository->save($proposal);
        }
        
        return [
            'orderId' => $orderId,
            'proposalId' => $proposal->getId()->getValue()
        ];
    }
}
