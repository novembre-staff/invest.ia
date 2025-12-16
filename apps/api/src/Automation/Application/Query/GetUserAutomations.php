<?php

declare(strict_types=1);

namespace App\Automation\Application\Query;

final readonly class GetUserAutomations
{
    public function __construct(
        public string $userId
    ) {
    }
}
