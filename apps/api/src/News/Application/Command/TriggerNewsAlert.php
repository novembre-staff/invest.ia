<?php

declare(strict_types=1);

namespace App\News\Application\Command;

final readonly class TriggerNewsAlert
{
    public function __construct(
        public string $articleId,
        public string $reason = 'high_importance'
    ) {}
}
