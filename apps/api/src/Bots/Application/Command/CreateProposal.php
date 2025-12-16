<?php

declare(strict_types=1);

namespace App\Bots\Application\Command;

use App\Identity\Domain\ValueObject\UserId;
use App\Strategy\Domain\ValueObject\StrategyId;

final readonly class CreateProposal
{
    /**
     * @param array<string, mixed> $riskFactors
     */
    public function __construct(
        public string $userId,
        public string $strategyId,
        public string $symbol,
        public string $side,
        public string $quantity,
        public string $rationale,
        public array $riskFactors,
        public string $riskScore,
        public ?string $limitPrice = null,
        public ?string $expectedReturn = null,
        public ?string $stopLoss = null,
        public ?string $takeProfit = null,
        public int $expirationMinutes = 30
    ) {}
}
