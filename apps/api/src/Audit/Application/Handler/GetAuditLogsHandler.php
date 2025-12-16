<?php

declare(strict_types=1);

namespace App\Audit\Application\Handler;

use App\Audit\Application\DTO\AuditLogDTO;
use App\Audit\Application\Query\GetAuditLogs;
use App\Audit\Domain\Repository\AuditLogRepositoryInterface;
use App\Audit\Domain\ValueObject\AuditAction;
use App\Audit\Domain\ValueObject\AuditSeverity;
use App\Identity\Domain\ValueObject\UserId;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class GetAuditLogsHandler
{
    public function __construct(
        private readonly AuditLogRepositoryInterface $auditLogRepository
    ) {}

    public function __invoke(GetAuditLogs $query): array
    {
        $startDate = $query->startDate !== null 
            ? new \DateTimeImmutable($query->startDate)
            : null;

        $endDate = $query->endDate !== null 
            ? new \DateTimeImmutable($query->endDate)
            : null;

        // Déterminer quelle méthode du repository utiliser
        $logs = match (true) {
            $query->userId !== null => $this->auditLogRepository->findByUserId(
                UserId::fromString($query->userId),
                $startDate,
                $endDate,
                $query->limit
            ),
            $query->action !== null => $this->auditLogRepository->findByAction(
                AuditAction::from($query->action),
                $startDate,
                $endDate,
                $query->limit
            ),
            $query->severity !== null => $this->auditLogRepository->findBySeverity(
                AuditSeverity::from($query->severity),
                $startDate,
                $endDate,
                $query->limit
            ),
            $query->entityType !== null && $query->entityId !== null => 
                $this->auditLogRepository->findByEntity(
                    $query->entityType,
                    $query->entityId,
                    $query->limit
                ),
            default => $this->auditLogRepository->findCriticalLogs($startDate, $query->limit)
        };

        return array_map(
            fn($log) => AuditLogDTO::fromDomain($log),
            $logs
        );
    }
}
