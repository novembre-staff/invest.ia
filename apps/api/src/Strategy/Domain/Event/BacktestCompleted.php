<?php

declare(strict_types=1);

namespace App\Strategy\Domain\Event;

use App\Identity\Domain\ValueObject\UserId;
use App\Strategy\Domain\ValueObject\StrategyId;

final readonly class BacktestCompleted
{
    public function __construct(
        public StrategyId $strategyId,
        public UserId $userId,
        public bool $passed,
        public array $results,
        public \DateTimeImmutable $occurredAt
    ) {
    }

    public static function now(
        StrategyId $strategyId,
        UserId $userId,
        bool $passed,
        array $results
    ): self {
        return new self($strategyId, $userId, $passed, $results, new \DateTimeImmutable());
    }
}
