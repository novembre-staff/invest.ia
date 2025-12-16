<?php

declare(strict_types=1);

namespace App\Audit\Application\Command;

final readonly class LogAuditEvent
{
    public function __construct(
        public string $userId,
        public string $action,
        public string $entityType,
        public string $entityId,
        public ?string $severity = null,
        public array $metadata = [],
        public ?string $ipAddress = null,
        public ?string $userAgent = null
    ) {}
}
