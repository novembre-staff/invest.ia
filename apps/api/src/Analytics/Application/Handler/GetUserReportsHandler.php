<?php

declare(strict_types=1);

namespace App\Analytics\Application\Handler;

use App\Analytics\Application\DTO\PerformanceReportDTO;
use App\Analytics\Application\Query\GetUserReports;
use App\Analytics\Domain\Repository\PerformanceReportRepositoryInterface;
use App\Analytics\Domain\ValueObject\ReportType;
use App\Identity\Domain\ValueObject\UserId;

class GetUserReportsHandler
{
    public function __construct(
        private PerformanceReportRepositoryInterface $reportRepository
    ) {
    }

    public function __invoke(GetUserReports $query): array
    {
        $userId = UserId::fromString($query->userId);

        if ($query->type !== null) {
            $type = ReportType::from($query->type);
            $reports = $this->reportRepository->findByUserIdAndType($userId, $type, $query->limit);
        } else {
            $reports = $this->reportRepository->findByUserId($userId, $query->limit);
        }

        return array_map(
            fn($report) => PerformanceReportDTO::fromDomain($report),
            $reports
        );
    }
}
