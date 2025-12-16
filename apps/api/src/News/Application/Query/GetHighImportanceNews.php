<?php

declare(strict_types=1);

namespace App\News\Application\Query;

final readonly class GetHighImportanceNews
{
    public function __construct(
        public int $minScore = 75,
        public int $limit = 20
    ) {
    }
}
