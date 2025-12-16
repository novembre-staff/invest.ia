<?php

declare(strict_types=1);

namespace App\Audit\Domain\Repository;

use App\Audit\Domain\Model\AuditLog;
use App\Audit\Domain\ValueObject\AuditAction;
use App\Audit\Domain\ValueObject\AuditLogId;
use App\Audit\Domain\ValueObject\AuditSeverity;
use App\Identity\Domain\ValueObject\UserId;

interface AuditLogRepositoryInterface
{
    public function save(AuditLog $auditLog): void;

    public function findById(AuditLogId $id): ?AuditLog;

    /**
     * @return AuditLog[]
     */
    public function findByUserId(
        UserId $userId,
        ?\DateTimeImmutable $startDate = null,
        ?\DateTimeImmutable $endDate = null,
        ?int $limit = null
    ): array;

    /**
     * @return AuditLog[]
     */
    public function findByAction(
        AuditAction $action,
        ?\DateTimeImmutable $startDate = null,
        ?\DateTimeImmutable $endDate = null,
        ?int $limit = null
    ): array;

    /**
     * @return AuditLog[]
     */
    public function findBySeverity(
        AuditSeverity $severity,
        ?\DateTimeImmutable $startDate = null,
        ?\DateTimeImmutable $endDate = null,
        ?int $limit = null
    ): array;

    /**
     * @return AuditLog[]
     */
    public function findByEntity(
        string $entityType,
        string $entityId,
        ?int $limit = null
    ): array;

    /**
     * @return AuditLog[]
     */
    public function findCriticalLogs(
        ?\DateTimeImmutable $startDate = null,
        ?int $limit = null
    ): array;
}
