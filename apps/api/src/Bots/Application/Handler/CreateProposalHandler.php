<?php

declare(strict_types=1);

namespace App\Bots\Application\Handler;

use App\Bots\Application\Command\CreateProposal;
use App\Bots\Application\DTO\ProposalDTO;
use App\Bots\Domain\Event\ProposalCreated;
use App\Bots\Domain\Model\Proposal;
use App\Bots\Domain\Repository\ProposalRepositoryInterface;
use App\Bots\Domain\ValueObject\ProposalId;
use App\Identity\Domain\ValueObject\UserId;
use App\Strategy\Domain\ValueObject\StrategyId;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class CreateProposalHandler
{
    public function __construct(
        private readonly ProposalRepositoryInterface $proposalRepository,
        private readonly MessageBusInterface $eventBus
    ) {}

    public function __invoke(CreateProposal $command): ProposalDTO
    {
        $proposalId = ProposalId::generate();
        $userId = UserId::fromString($command->userId);
        $strategyId = StrategyId::fromString($command->strategyId);
        
        $proposal = new Proposal(
            $proposalId,
            $userId,
            $strategyId,
            $command->symbol,
            $command->side,
            $command->quantity,
            $command->rationale,
            $command->riskFactors,
            $command->riskScore,
            $command->limitPrice,
            $command->expectedReturn,
            $command->stopLoss,
            $command->takeProfit,
            $command->expirationMinutes
        );
        
        $this->proposalRepository->save($proposal);
        
        // Dispatch domain event
        $this->eventBus->dispatch(new ProposalCreated(
            $proposal->getId(),
            $proposal->getUserId(),
            $proposal->getStrategyId(),
            $proposal->getSymbol(),
            $proposal->getSide(),
            $proposal->getQuantity(),
            $proposal->getRiskScore(),
            $proposal->getExpiresAt(),
            new \DateTimeImmutable()
        ));
        
        return ProposalDTO::fromAggregate($proposal);
    }
}
