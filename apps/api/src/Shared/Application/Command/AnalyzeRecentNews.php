<?php

declare(strict_types=1);

namespace App\Shared\Application\Command;

/**
 * Command to trigger scheduled task for analyzing recent news articles
 */
final class AnalyzeRecentNews
{
    public function __construct(
        private readonly int $maxArticles = 50,
        private readonly ?int $hoursBack = 6
    ) {
    }

    public function maxArticles(): int
    {
        return $this->maxArticles;
    }

    public function hoursBack(): ?int
    {
        return $this->hoursBack;
    }
}
