<?php

declare(strict_types=1);

namespace App\News\Domain\Event;

use App\News\Domain\ValueObject\NewsArticleId;

final readonly class HighImportanceNewsDetected
{
    public function __construct(
        public NewsArticleId $articleId,
        public string $title,
        public int $importanceScore,
        public array $relatedSymbols,
        public \DateTimeImmutable $occurredAt
    ) {
    }

    public static function now(
        NewsArticleId $articleId,
        string $title,
        int $importanceScore,
        array $relatedSymbols
    ): self {
        return new self(
            $articleId,
            $title,
            $importanceScore,
            $relatedSymbols,
            new \DateTimeImmutable()
        );
    }
}
