<?php

declare(strict_types=1);

namespace App\Risk\Application\Query;

final readonly class GetRiskProfile
{
    public function __construct(
        public string $userId
    ) {
    }
}
