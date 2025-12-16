<?php

declare(strict_types=1);

namespace App\News\Application\Query;

final readonly class GetNewsFeed
{
    public function __construct(
        public ?string $category = null,
        public int $limit = 50
    ) {
    }
}
