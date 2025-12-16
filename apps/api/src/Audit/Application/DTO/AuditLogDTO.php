<?php

declare(strict_types=1);

namespace App\Audit\Application\DTO;

use App\Audit\Domain\Model\AuditLog;

final readonly class AuditLogDTO
{
    public function __construct(
        public string $id,
        public string $userId,
        public string $action,
        public string $entityType,
        public string $entityId,
        public string $severity,
        public array $metadata,
        public ?string $ipAddress,
        public ?string $userAgent,
        public string $occurredAt
    ) {}

    public static function fromDomain(AuditLog $auditLog): self
    {
        return new self(
            id: $auditLog->getId()->getValue(),
            userId: $auditLog->getUserId()->getValue(),
            action: $auditLog->getAction()->value,
            entityType: $auditLog->getEntityType(),
            entityId: $auditLog->getEntityId(),
            severity: $auditLog->getSeverity()->value,
            metadata: $auditLog->getMetadata(),
            ipAddress: $auditLog->getIpAddress(),
            userAgent: $auditLog->getUserAgent(),
            occurredAt: $auditLog->getOccurredAt()->format(\DateTimeInterface::ATOM)
        );
    }
}
