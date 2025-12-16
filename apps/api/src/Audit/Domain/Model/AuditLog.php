<?php

declare(strict_types=1);

namespace App\Audit\Domain\Model;

use App\Audit\Domain\ValueObject\AuditLogId;
use App\Audit\Domain\ValueObject\AuditAction;
use App\Audit\Domain\ValueObject\AuditSeverity;
use App\Identity\Domain\ValueObject\UserId;

/**
 * Audit Log Aggregate
 * Trace toutes les actions importantes dans le système
 */
class AuditLog
{
    private AuditLogId $id;
    private UserId $userId;
    private AuditAction $action;
    private string $entityType;
    private string $entityId;
    private AuditSeverity $severity;
    private array $metadata;
    private ?string $ipAddress;
    private ?string $userAgent;
    private \DateTimeImmutable $occurredAt;

    public function __construct(
        AuditLogId $id,
        UserId $userId,
        AuditAction $action,
        string $entityType,
        string $entityId,
        AuditSeverity $severity = AuditSeverity::INFO,
        array $metadata = [],
        ?string $ipAddress = null,
        ?string $userAgent = null
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->action = $action;
        $this->entityType = $entityType;
        $this->entityId = $entityId;
        $this->severity = $severity;
        $this->metadata = $metadata;
        $this->ipAddress = $ipAddress;
        $this->userAgent = $userAgent;
        $this->occurredAt = new \DateTimeImmutable();
    }

    public static function create(
        UserId $userId,
        AuditAction $action,
        string $entityType,
        string $entityId,
        AuditSeverity $severity = AuditSeverity::INFO,
        array $metadata = [],
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): self {
        return new self(
            AuditLogId::generate(),
            $userId,
            $action,
            $entityType,
            $entityId,
            $severity,
            $metadata,
            $ipAddress,
            $userAgent
        );
    }

    // Getters

    public function getId(): AuditLogId
    {
        return $this->id;
    }

    public function getUserId(): UserId
    {
        return $this->userId;
    }

    public function getAction(): AuditAction
    {
        return $this->action;
    }

    public function getEntityType(): string
    {
        return $this->entityType;
    }

    public function getEntityId(): string
    {
        return $this->entityId;
    }

    public function getSeverity(): AuditSeverity
    {
        return $this->severity;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function getOccurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }

    public function isHighSeverity(): bool
    {
        return $this->severity === AuditSeverity::WARNING 
            || $this->severity === AuditSeverity::ERROR 
            || $this->severity === AuditSeverity::CRITICAL;
    }
}
