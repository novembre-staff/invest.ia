<?php

declare(strict_types=1);

namespace App\Analytics\Application\Query;

final readonly class GetUserReports
{
    public function __construct(
        public string $userId,
        public ?string $type = null,
        public int $limit = 20
    ) {
    }
}
