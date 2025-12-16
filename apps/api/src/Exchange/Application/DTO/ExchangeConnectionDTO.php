<?php

declare(strict_types=1);

namespace App\Exchange\Application\DTO;

use App\Exchange\Domain\Model\ExchangeConnection;

final readonly class ExchangeConnectionDTO
{
    private function __construct(
        public string $id,
        public string $exchangeName,
        public ?string $label,
        public bool $isActive,
        public string $connectedAt,
        public ?string $lastSyncAt
    ) {
    }

    public static function fromAggregate(ExchangeConnection $connection): self
    {
        return new self(
            id: $connection->getId()->getValue(),
            exchangeName: $connection->getExchangeName(),
            label: $connection->getLabel(),
            isActive: $connection->isActive(),
            connectedAt: $connection->getConnectedAt()->format(\DateTimeInterface::ATOM),
            lastSyncAt: $connection->getLastSyncAt()?->format(\DateTimeInterface::ATOM)
        );
    }
}
