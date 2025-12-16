<?php

declare(strict_types=1);

namespace App\Analytics\Infrastructure\Persistence\Doctrine;

use App\Analytics\Domain\Model\PerformanceReport;
use App\Analytics\Domain\Repository\PerformanceReportRepositoryInterface;
use App\Analytics\Domain\ValueObject\ReportId;
use App\Analytics\Domain\ValueObject\ReportType;
use App\Identity\Domain\ValueObject\UserId;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DoctrinePerformanceReportRepository extends ServiceEntityRepository implements PerformanceReportRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PerformanceReport::class);
    }

    public function save(PerformanceReport $report): void
    {
        $this->getEntityManager()->persist($report);
        $this->getEntityManager()->flush();
    }

    public function findById(ReportId $id): ?PerformanceReport
    {
        return $this->find($id->toString());
    }

    public function findByUserId(UserId $userId, int $limit = 20): array
    {
        return $this->findBy(
            ['userId' => $userId->toString()],
            ['createdAt' => 'DESC'],
            $limit
        );
    }

    public function findByUserIdAndType(UserId $userId, ReportType $type, int $limit = 10): array
    {
        return $this->findBy(
            [
                'userId' => $userId->toString(),
                'type' => $type->value
            ],
            ['createdAt' => 'DESC'],
            $limit
        );
    }

    public function delete(PerformanceReport $report): void
    {
        $this->getEntityManager()->remove($report);
        $this->getEntityManager()->flush();
    }
}
