<?php

declare(strict_types=1);

namespace App\Bots\Domain\Event;

use App\Bots\Domain\ValueObject\ProposalId;
use App\Identity\Domain\ValueObject\UserId;
use App\Strategy\Domain\ValueObject\StrategyId;

final readonly class ProposalCreated
{
    public function __construct(
        public ProposalId $proposalId,
        public UserId $userId,
        public StrategyId $strategyId,
        public string $symbol,
        public string $side,
        public string $quantity,
        public string $riskScore,
        public \DateTimeImmutable $expiresAt,
        public \DateTimeImmutable $occurredAt
    ) {}
}
