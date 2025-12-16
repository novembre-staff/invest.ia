<?php

declare(strict_types=1);

namespace App\Audit\Infrastructure\Persistence\Doctrine;

use App\Audit\Domain\Model\AuditLog;
use App\Audit\Domain\Repository\AuditLogRepositoryInterface;
use App\Audit\Domain\ValueObject\AuditAction;
use App\Audit\Domain\ValueObject\AuditLogId;
use App\Audit\Domain\ValueObject\AuditSeverity;
use App\Identity\Domain\ValueObject\UserId;
use Doctrine\ORM\EntityManagerInterface;

class DoctrineAuditLogRepository implements AuditLogRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {}

    public function save(AuditLog $auditLog): void
    {
        $this->entityManager->persist($auditLog);
        $this->entityManager->flush();
    }

    public function findById(AuditLogId $id): ?AuditLog
    {
        return $this->entityManager->find(AuditLog::class, $id->getValue());
    }

    public function findByUserId(
        UserId $userId,
        ?\DateTimeImmutable $startDate = null,
        ?\DateTimeImmutable $endDate = null,
        ?int $limit = null
    ): array {
        $qb = $this->entityManager->createQueryBuilder();
        
        $qb->select('al')
            ->from(AuditLog::class, 'al')
            ->where('al.userId = :userId')
            ->setParameter('userId', $userId->getValue())
            ->orderBy('al.occurredAt', 'DESC');

        if ($startDate !== null) {
            $qb->andWhere('al.occurredAt >= :startDate')
                ->setParameter('startDate', $startDate);
        }

        if ($endDate !== null) {
            $qb->andWhere('al.occurredAt <= :endDate')
                ->setParameter('endDate', $endDate);
        }

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    public function findByAction(
        AuditAction $action,
        ?\DateTimeImmutable $startDate = null,
        ?\DateTimeImmutable $endDate = null,
        ?int $limit = null
    ): array {
        $qb = $this->entityManager->createQueryBuilder();
        
        $qb->select('al')
            ->from(AuditLog::class, 'al')
            ->where('al.action = :action')
            ->setParameter('action', $action->value)
            ->orderBy('al.occurredAt', 'DESC');

        if ($startDate !== null) {
            $qb->andWhere('al.occurredAt >= :startDate')
                ->setParameter('startDate', $startDate);
        }

        if ($endDate !== null) {
            $qb->andWhere('al.occurredAt <= :endDate')
                ->setParameter('endDate', $endDate);
        }

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    public function findBySeverity(
        AuditSeverity $severity,
        ?\DateTimeImmutable $startDate = null,
        ?\DateTimeImmutable $endDate = null,
        ?int $limit = null
    ): array {
        $qb = $this->entityManager->createQueryBuilder();
        
        $qb->select('al')
            ->from(AuditLog::class, 'al')
            ->where('al.severity = :severity')
            ->setParameter('severity', $severity->value)
            ->orderBy('al.occurredAt', 'DESC');

        if ($startDate !== null) {
            $qb->andWhere('al.occurredAt >= :startDate')
                ->setParameter('startDate', $startDate);
        }

        if ($endDate !== null) {
            $qb->andWhere('al.occurredAt <= :endDate')
                ->setParameter('endDate', $endDate);
        }

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    public function findByEntity(
        string $entityType,
        string $entityId,
        ?int $limit = null
    ): array {
        $qb = $this->entityManager->createQueryBuilder();
        
        $qb->select('al')
            ->from(AuditLog::class, 'al')
            ->where('al.entityType = :entityType')
            ->andWhere('al.entityId = :entityId')
            ->setParameter('entityType', $entityType)
            ->setParameter('entityId', $entityId)
            ->orderBy('al.occurredAt', 'DESC');

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    public function findCriticalLogs(
        ?\DateTimeImmutable $startDate = null,
        ?int $limit = null
    ): array {
        $qb = $this->entityManager->createQueryBuilder();
        
        $qb->select('al')
            ->from(AuditLog::class, 'al')
            ->where('al.severity IN (:severities)')
            ->setParameter('severities', ['critical', 'error'])
            ->orderBy('al.occurredAt', 'DESC');

        if ($startDate !== null) {
            $qb->andWhere('al.occurredAt >= :startDate')
                ->setParameter('startDate', $startDate);
        }

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }
}
