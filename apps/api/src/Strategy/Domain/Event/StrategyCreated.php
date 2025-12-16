<?php

declare(strict_types=1);

namespace App\Strategy\Domain\Event;

use App\Identity\Domain\ValueObject\UserId;
use App\Strategy\Domain\ValueObject\StrategyId;
use App\Strategy\Domain\ValueObject\StrategyType;

final readonly class StrategyCreated
{
    public function __construct(
        public StrategyId $strategyId,
        public UserId $userId,
        public string $name,
        public StrategyType $type,
        public array $symbols,
        public \DateTimeImmutable $occurredAt
    ) {
    }

    public static function now(
        StrategyId $strategyId,
        UserId $userId,
        string $name,
        StrategyType $type,
        array $symbols
    ): self {
        return new self($strategyId, $userId, $name, $type, $symbols, new \DateTimeImmutable());
    }
}
