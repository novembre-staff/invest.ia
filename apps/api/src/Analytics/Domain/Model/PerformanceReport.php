<?php

declare(strict_types=1);

namespace App\Analytics\Domain\Model;

use App\Analytics\Domain\ValueObject\AssetAllocation;
use App\Analytics\Domain\ValueObject\PerformanceMetrics;
use App\Analytics\Domain\ValueObject\ReportId;
use App\Analytics\Domain\ValueObject\ReportType;
use App\Analytics\Domain\ValueObject\TimePeriod;
use App\Identity\Domain\ValueObject\UserId;

class PerformanceReport
{
    private ReportId $id;
    private UserId $userId;
    private ReportType $type;
    private TimePeriod $period;
    private ?\DateTimeImmutable $startDate;
    private \DateTimeImmutable $endDate;
    private ?PerformanceMetrics $metrics = null;
    private ?AssetAllocation $allocation = null;
    private array $data = [];
    private \DateTimeImmutable $createdAt;

    private function __construct(
        ReportId $id,
        UserId $userId,
        ReportType $type,
        TimePeriod $period,
        ?\DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate,
        array $data = []
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->type = $type;
        $this->period = $period;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->data = $data;
        $this->createdAt = new \DateTimeImmutable();
    }

    public static function create(
        UserId $userId,
        ReportType $type,
        TimePeriod $period,
        ?\DateTimeImmutable $startDate = null,
        ?\DateTimeImmutable $endDate = null,
        array $data = []
    ): self {
        $endDate = $endDate ?? new \DateTimeImmutable();
        
        if ($startDate === null && $period !== TimePeriod::CUSTOM) {
            $startDate = $period->getStartDate($endDate);
        }

        return new self(
            ReportId::generate(),
            $userId,
            $type,
            $period,
            $startDate,
            $endDate,
            $data
        );
    }

    public function setMetrics(PerformanceMetrics $metrics): void
    {
        $this->metrics = $metrics;
    }

    public function setAllocation(AssetAllocation $allocation): void
    {
        $this->allocation = $allocation;
    }

    public function addData(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    public function setData(array $data): void
    {
        $this->data = $data;
    }

    // Getters
    public function getId(): ReportId
    {
        return $this->id;
    }

    public function getUserId(): UserId
    {
        return $this->userId;
    }

    public function getType(): ReportType
    {
        return $this->type;
    }

    public function getPeriod(): TimePeriod
    {
        return $this->period;
    }

    public function getStartDate(): ?\DateTimeImmutable
    {
        return $this->startDate;
    }

    public function getEndDate(): \DateTimeImmutable
    {
        return $this->endDate;
    }

    public function getMetrics(): ?PerformanceMetrics
    {
        return $this->metrics;
    }

    public function getAllocation(): ?AssetAllocation
    {
        return $this->allocation;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getDurationInDays(): int
    {
        if ($this->startDate === null) {
            return 0;
        }

        return (int) $this->startDate->diff($this->endDate)->days;
    }
}
