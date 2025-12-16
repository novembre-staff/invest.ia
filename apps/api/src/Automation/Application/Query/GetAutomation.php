<?php

declare(strict_types=1);

namespace App\Automation\Application\Query;

final readonly class GetAutomation
{
    public function __construct(
        public string $automationId,
        public string $userId
    ) {
    }
}
