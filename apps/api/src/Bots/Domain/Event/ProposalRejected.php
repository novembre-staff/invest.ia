<?php

declare(strict_types=1);

namespace App\Bots\Domain\Event;

use App\Bots\Domain\ValueObject\ProposalId;
use App\Identity\Domain\ValueObject\UserId;

final readonly class ProposalRejected
{
    public function __construct(
        public ProposalId $proposalId,
        public UserId $userId,
        public string $symbol,
        public \DateTimeImmutable $occurredAt
    ) {}
}
