<?php

declare(strict_types=1);

namespace App\Audit\Application\Handler;

use App\Audit\Application\Command\LogAuditEvent;
use App\Audit\Domain\Model\AuditLog;
use App\Audit\Domain\Repository\AuditLogRepositoryInterface;
use App\Audit\Domain\ValueObject\AuditAction;
use App\Audit\Domain\ValueObject\AuditSeverity;
use App\Identity\Domain\ValueObject\UserId;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class LogAuditEventHandler
{
    public function __construct(
        private readonly AuditLogRepositoryInterface $auditLogRepository
    ) {}

    public function __invoke(LogAuditEvent $command): void
    {
        $userId = UserId::fromString($command->userId);
        $action = AuditAction::from($command->action);
        $severity = $command->severity !== null 
            ? AuditSeverity::from($command->severity)
            : ($action->isCritical() ? AuditSeverity::CRITICAL : AuditSeverity::INFO);

        $auditLog = AuditLog::create(
            userId: $userId,
            action: $action,
            entityType: $command->entityType,
            entityId: $command->entityId,
            severity: $severity,
            metadata: $command->metadata,
            ipAddress: $command->ipAddress,
            userAgent: $command->userAgent
        );

        $this->auditLogRepository->save($auditLog);
    }
}
