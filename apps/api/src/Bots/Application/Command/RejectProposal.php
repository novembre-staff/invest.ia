<?php

declare(strict_types=1);

namespace App\Bots\Application\Command;

final readonly class RejectProposal
{
    public function __construct(
        public string $proposalId,
        public string $userId,
        public string $reason = ''
    ) {}
}
