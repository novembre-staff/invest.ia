<?php

declare(strict_types=1);

namespace App\News\Domain\Service;

use App\News\Domain\ValueObject\ImportanceScore;

/**
 * Domain service to calculate news importance score
 * Based on keywords, source credibility, recency, etc.
 */
interface ImportanceScorerInterface
{
    /**
     * Calculate importance score for a news article
     * 
     * @param string[] $relatedSymbols
     */
    public function calculateScore(
        string $title,
        string $summary,
        string $sourceName,
        array $relatedSymbols,
        \DateTimeImmutable $publishedAt
    ): ImportanceScore;
}
