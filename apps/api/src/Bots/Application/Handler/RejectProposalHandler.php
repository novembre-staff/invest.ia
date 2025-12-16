<?php

declare(strict_types=1);

namespace App\Bots\Application\Handler;

use App\Bots\Application\Command\RejectProposal;
use App\Bots\Domain\Event\ProposalRejected;
use App\Bots\Domain\Repository\ProposalRepositoryInterface;
use App\Bots\Domain\ValueObject\ProposalId;
use App\Identity\Domain\ValueObject\UserId;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class RejectProposalHandler
{
    public function __construct(
        private readonly ProposalRepositoryInterface $proposalRepository,
        private readonly MessageBusInterface $eventBus
    ) {}

    public function __invoke(RejectProposal $command): void
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
        
        // Refuser la proposition
        $proposal->reject($command->reason);
        $this->proposalRepository->save($proposal);
        
        // Dispatch domain event
        $this->eventBus->dispatch(new ProposalRejected(
            $proposal->getId(),
            $proposal->getUserId(),
            $proposal->getSymbol(),
            new \DateTimeImmutable()
        ));
    }
}
