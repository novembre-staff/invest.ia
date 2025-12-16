<?php

declare(strict_types=1);

namespace App\Analytics\Application\Handler;

use App\Analytics\Application\DTO\PerformanceReportDTO;
use App\Analytics\Application\Query\GetPerformanceReport;
use App\Analytics\Domain\Repository\PerformanceReportRepositoryInterface;
use App\Analytics\Domain\ValueObject\ReportId;

class GetPerformanceReportHandler
{
    public function __construct(
        private PerformanceReportRepositoryInterface $reportRepository
    ) {
    }

    public function __invoke(GetPerformanceReport $query): ?PerformanceReportDTO
    {
        $reportId = ReportId::fromString($query->reportId);
        $report = $this->reportRepository->findById($reportId);

        if ($report === null) {
            return null;
        }

        // Verify ownership
        if ($report->getUserId()->toString() !== $query->userId) {
            throw new \DomainException('Access denied');
        }

        return PerformanceReportDTO::fromDomain($report);
    }
}
