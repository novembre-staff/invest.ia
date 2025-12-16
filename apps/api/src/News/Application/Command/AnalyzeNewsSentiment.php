<?php

declare(strict_types=1);

namespace App\News\Application\Command;

final readonly class AnalyzeNewsSentiment
{
    public function __construct(
        public string $articleId
    ) {}
}
