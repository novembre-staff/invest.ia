<?php

declare(strict_types=1);

namespace App\Strategy\Application\Query;

final readonly class GetPositionClosureExplanation
{
    public function __construct(
        public string $positionId
    ) {
    }
}
