<?php

declare(strict_types=1);

namespace App\News\Application\Query;

final readonly class GetNewsBySymbols
{
    /**
     * @param string[] $symbols
     */
    public function __construct(
        public array $symbols,
        public int $limit = 20
    ) {
    }
}
