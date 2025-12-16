<?php

declare(strict_types=1);

namespace App\Analytics\Domain\Repository;

use App\Analytics\Domain\Model\PerformanceReport;
use App\Analytics\Domain\ValueObject\ReportId;
use App\Analytics\Domain\ValueObject\ReportType;
use App\Identity\Domain\ValueObject\UserId;

interface PerformanceReportRepositoryInterface
{
    public function save(PerformanceReport $report): void;

    public function findById(ReportId $id): ?PerformanceReport;

    public function findByUserId(UserId $userId, int $limit = 20): array;

    public function findByUserIdAndType(UserId $userId, ReportType $type, int $limit = 10): array;

    public function delete(PerformanceReport $report): void;
}
