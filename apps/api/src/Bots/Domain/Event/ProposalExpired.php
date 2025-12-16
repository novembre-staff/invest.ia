<?php

declare(strict_types=1);

namespace App\Bots\Domain\Event;

use App\Bots\Domain\ValueObject\ProposalId;

final readonly class ProposalExpired
{
    public function __construct(
        public ProposalId $proposalId,
        public \DateTimeImmutable $occurredAt
    ) {}
}
