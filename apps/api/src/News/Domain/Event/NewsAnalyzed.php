<?php

declare(strict_types=1);

namespace App\News\Domain\Event;

use DateTimeImmutable;

final class NewsAnalyzed
{
    public function __construct(
        private readonly string $newsId,
        private readonly float $sentimentScore,
        private readonly string $sentimentLabel,
        private readonly string $importance,
        private readonly ?array $affectedSymbols,
        private readonly DateTimeImmutable $occurredAt
    ) {
    }

    public function newsId(): string
    {
        return $this->newsId;
    }

    public function sentimentScore(): float
    {
        return $this->sentimentScore;
    }

    public function sentimentLabel(): string
    {
        return $this->sentimentLabel;
    }

    public function importance(): string
    {
        return $this->importance;
    }

    public function affectedSymbols(): ?array
    {
        return $this->affectedSymbols;
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
