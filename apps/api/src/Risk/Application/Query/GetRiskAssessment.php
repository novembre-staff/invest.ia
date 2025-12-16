<?php

declare(strict_types=1);

namespace App\Risk\Application\Query;

final readonly class GetRiskAssessment
{
    public function __construct(
        public string $userId
    ) {
    }
}
