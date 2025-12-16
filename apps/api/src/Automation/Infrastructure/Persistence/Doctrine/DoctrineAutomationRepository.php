<?php

declare(strict_types=1);

namespace App\Automation\Infrastructure\Persistence\Doctrine;

use App\Automation\Domain\Model\Automation;
use App\Automation\Domain\Repository\AutomationRepositoryInterface;
use App\Automation\Domain\ValueObject\AutomationId;
use App\Automation\Domain\ValueObject\AutomationStatus;
use App\Identity\Domain\ValueObject\UserId;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DoctrineAutomationRepository extends ServiceEntityRepository implements AutomationRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Automation::class);
    }

    public function save(Automation $automation): void
    {
        $this->getEntityManager()->persist($automation);
        $this->getEntityManager()->flush();
    }

    public function findById(AutomationId $id): ?Automation
    {
        return $this->find($id->toString());
    }

    public function findByUserId(UserId $userId): array
    {
        return $this->findBy(['userId' => $userId->toString()], ['createdAt' => 'DESC']);
    }

    public function findByStatus(AutomationStatus $status): array
    {
        return $this->findBy(['status' => $status->value]);
    }

    public function findActiveAutomations(): array
    {
        return $this->findBy(['status' => AutomationStatus::ACTIVE->value]);
    }

    public function findDueForExecution(\DateTimeImmutable $now): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.status = :status')
            ->andWhere('a.nextExecutionAt IS NOT NULL')
            ->andWhere('a.nextExecutionAt <= :now')
            ->setParameter('status', AutomationStatus::ACTIVE->value)
            ->setParameter('now', $now)
            ->getQuery()
            ->getResult();
    }

    public function delete(Automation $automation): void
    {
        $this->getEntityManager()->remove($automation);
        $this->getEntityManager()->flush();
    }
}
