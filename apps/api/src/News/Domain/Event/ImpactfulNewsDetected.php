<?php

declare(strict_types=1);

namespace App\News\Domain\Event;

final readonly class ImpactfulNewsDetected
{
    public function __construct(
        public string $positionId,
        public string $symbol,
        public string $articleId,
        public float $sentiment,
        public string $importance,
        public \DateTimeImmutable $occurredAt
    ) {
    }

    public static function now(
        string $positionId,
        string $symbol,
        string $articleId,
        float $sentiment,
        string $importance
    ): self {
        return new self(
            positionId: $positionId,
            symbol: $symbol,
            articleId: $articleId,
            sentiment: $sentiment,
            importance: $importance,
            occurredAt: new \DateTimeImmutable()
        );
    }
}
