<?php

declare(strict_types=1);

namespace App\Strategy\Domain\Event;

use App\Identity\Domain\ValueObject\UserId;
use App\Strategy\Domain\ValueObject\StrategyId;

final readonly class StrategyActivated
{
    public function __construct(
        public StrategyId $strategyId,
        public UserId $userId,
        public string $name,
        public array $symbols,
        public \DateTimeImmutable $occurredAt
    ) {
    }

    public static function now(
        StrategyId $strategyId,
        UserId $userId,
        string $name,
        array $symbols
    ): self {
        return new self($strategyId, $userId, $name, $symbols, new \DateTimeImmutable());
    }
}
