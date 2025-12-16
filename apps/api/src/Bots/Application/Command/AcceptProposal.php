<?php

declare(strict_types=1);

namespace App\Bots\Application\Command;

final readonly class AcceptProposal
{
    public function __construct(
        public string $proposalId,
        public string $userId
    ) {}
}
