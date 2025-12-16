<?php

declare(strict_types=1);

namespace App\Audit\Application\Query;

final readonly class GetAuditLogs
{
    public function __construct(
        public ?string $userId = null,
        public ?string $action = null,
        public ?string $entityType = null,
        public ?string $entityId = null,
        public ?string $severity = null,
        public ?string $startDate = null,
        public ?string $endDate = null,
        public int $limit = 100
    ) {}
}
