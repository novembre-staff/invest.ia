<?php

declare(strict_types=1);

namespace App\Exchange\Domain\Event;

use App\Exchange\Domain\ValueObject\ExchangeConnectionId;
use App\Identity\Domain\ValueObject\UserId;

final readonly class ExchangeConnected
{
    public function __construct(
        public ExchangeConnectionId $connectionId,
        public UserId $userId,
        public string $exchangeName,
        public \DateTimeImmutable $occurredAt
    ) {
    }

    public static function now(
        ExchangeConnectionId $connectionId,
        UserId $userId,
        string $exchangeName
    ): self {
        return new self(
            $connectionId,
            $userId,
            $exchangeName,
            new \DateTimeImmutable()
        );
    }
}
