<?php

declare(strict_types=1);

namespace App\Analytics\Application\DTO;

use App\Analytics\Domain\Model\PerformanceReport;

final readonly class PerformanceReportDTO
{
    public function __construct(
        public string $id,
        public string $userId,
        public string $type,
        public string $period,
        public ?string $startDate,
        public string $endDate,
        public int $durationDays,
        public ?array $metrics,
        public ?array $allocation,
        public array $data,
        public string $createdAt
    ) {
    }

    public static function fromDomain(PerformanceReport $report): self
    {
        $metrics = null;
        if ($report->getMetrics() !== null) {
            $metrics = $report->getMetrics()->toArray();
        }

        $allocation = null;
        if ($report->getAllocation() !== null) {
            $alloc = $report->getAllocation();
            $allocation = [
                'allocations' => $alloc->getAllocations(),
                'total_value' => $alloc->getTotalValue(),
                'top_assets' => $alloc->getTopAssets(5),
                'diversification_score' => $alloc->getDiversificationScore()
            ];
        }

        return new self(
            id: $report->getId()->toString(),
            userId: $report->getUserId()->toString(),
            type: $report->getType()->value,
            period: $report->getPeriod()->value,
            startDate: $report->getStartDate()?->format('Y-m-d H:i:s'),
            endDate: $report->getEndDate()->format('Y-m-d H:i:s'),
            durationDays: $report->getDurationInDays(),
            metrics: $metrics,
            allocation: $allocation,
            data: $report->getData(),
            createdAt: $report->getCreatedAt()->format('Y-m-d H:i:s')
        );
    }
}
