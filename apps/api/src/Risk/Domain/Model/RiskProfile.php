<?php

declare(strict_types=1);

namespace App\Risk\Domain\Model;

use App\Identity\Domain\ValueObject\UserId;
use App\Risk\Domain\ValueObject\RiskLevel;
use App\Risk\Domain\ValueObject\RiskProfileId;

class RiskProfile
{
    private RiskProfileId $id;
    private UserId $userId;
    private RiskLevel $riskLevel;
    private ?float $maxPositionSizePercent;
    private ?float $maxPortfolioExposurePercent;
    private ?float $maxDailyLossPercent;
    private ?float $maxDrawdownPercent;
    private ?float $maxLeverage;
    private ?float $maxConcentrationPercent;
    private ?int $maxTradesPerDay;
    private bool $requireApprovalAboveLimit;
    private ?string $notes;
    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;

    private function __construct(
        RiskProfileId $id,
        UserId $userId,
        RiskLevel $riskLevel,
        ?float $maxPositionSizePercent = null,
        ?float $maxPortfolioExposurePercent = null,
        ?float $maxDailyLossPercent = null,
        ?float $maxDrawdownPercent = null,
        ?float $maxLeverage = 1.0,
        ?float $maxConcentrationPercent = null,
        ?int $maxTradesPerDay = null,
        bool $requireApprovalAboveLimit = true,
        ?string $notes = null
    ) {
        $this->validateLimits(
            $maxPositionSizePercent,
            $maxPortfolioExposurePercent,
            $maxDailyLossPercent,
            $maxDrawdownPercent,
            $maxLeverage,
            $maxConcentrationPercent,
            $maxTradesPerDay
        );

        $this->id = $id;
        $this->userId = $userId;
        $this->riskLevel = $riskLevel;
        $this->maxPositionSizePercent = $maxPositionSizePercent ?? $riskLevel->getMaxPositionSizePercent();
        $this->maxPortfolioExposurePercent = $maxPortfolioExposurePercent ?? 100.0;
        $this->maxDailyLossPercent = $maxDailyLossPercent ?? $riskLevel->getMaxDailyLossPercent();
        $this->maxDrawdownPercent = $maxDrawdownPercent ?? $riskLevel->getMaxDrawdownPercent();
        $this->maxLeverage = $maxLeverage;
        $this->maxConcentrationPercent = $maxConcentrationPercent ?? 50.0;
        $this->maxTradesPerDay = $maxTradesPerDay;
        $this->requireApprovalAboveLimit = $requireApprovalAboveLimit;
        $this->notes = $notes;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public static function create(
        UserId $userId,
        RiskLevel $riskLevel,
        ?float $maxPositionSizePercent = null,
        ?float $maxPortfolioExposurePercent = null,
        ?float $maxDailyLossPercent = null,
        ?float $maxDrawdownPercent = null,
        ?float $maxLeverage = 1.0,
        ?float $maxConcentrationPercent = null,
        ?int $maxTradesPerDay = null,
        bool $requireApprovalAboveLimit = true,
        ?string $notes = null
    ): self {
        return new self(
            RiskProfileId::generate(),
            $userId,
            $riskLevel,
            $maxPositionSizePercent,
            $maxPortfolioExposurePercent,
            $maxDailyLossPercent,
            $maxDrawdownPercent,
            $maxLeverage,
            $maxConcentrationPercent,
            $maxTradesPerDay,
            $requireApprovalAboveLimit,
            $notes
        );
    }

    public function updateLimits(
        ?float $maxPositionSizePercent,
        ?float $maxPortfolioExposurePercent,
        ?float $maxDailyLossPercent,
        ?float $maxDrawdownPercent,
        ?float $maxLeverage,
        ?float $maxConcentrationPercent,
        ?int $maxTradesPerDay
    ): void {
        $this->validateLimits(
            $maxPositionSizePercent,
            $maxPortfolioExposurePercent,
            $maxDailyLossPercent,
            $maxDrawdownPercent,
            $maxLeverage,
            $maxConcentrationPercent,
            $maxTradesPerDay
        );

        $this->maxPositionSizePercent = $maxPositionSizePercent;
        $this->maxPortfolioExposurePercent = $maxPortfolioExposurePercent;
        $this->maxDailyLossPercent = $maxDailyLossPercent;
        $this->maxDrawdownPercent = $maxDrawdownPercent;
        $this->maxLeverage = $maxLeverage;
        $this->maxConcentrationPercent = $maxConcentrationPercent;
        $this->maxTradesPerDay = $maxTradesPerDay;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function changeRiskLevel(RiskLevel $newLevel): void
    {
        $this->riskLevel = $newLevel;
        
        // Adjust limits to match new risk level if current limits exceed new level's defaults
        if ($this->maxPositionSizePercent && $this->maxPositionSizePercent > $newLevel->getMaxPositionSizePercent()) {
            $this->maxPositionSizePercent = $newLevel->getMaxPositionSizePercent();
        }
        
        if ($this->maxDailyLossPercent && $this->maxDailyLossPercent > $newLevel->getMaxDailyLossPercent()) {
            $this->maxDailyLossPercent = $newLevel->getMaxDailyLossPercent();
        }
        
        if ($this->maxDrawdownPercent && $this->maxDrawdownPercent > $newLevel->getMaxDrawdownPercent()) {
            $this->maxDrawdownPercent = $newLevel->getMaxDrawdownPercent();
        }

        $this->updatedAt = new \DateTimeImmutable();
    }

    public function updateNotes(?string $notes): void
    {
        $this->notes = $notes;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function isPositionSizeAllowed(float $positionSizePercent): bool
    {
        if ($this->maxPositionSizePercent === null) {
            return true;
        }
        return $positionSizePercent <= $this->maxPositionSizePercent;
    }

    public function isDailyLossAllowed(float $dailyLossPercent): bool
    {
        if ($this->maxDailyLossPercent === null) {
            return true;
        }
        return abs($dailyLossPercent) <= $this->maxDailyLossPercent;
    }

    public function isDrawdownAllowed(float $drawdownPercent): bool
    {
        if ($this->maxDrawdownPercent === null) {
            return true;
        }
        return $drawdownPercent <= $this->maxDrawdownPercent;
    }

    public function isLeverageAllowed(float $leverage): bool
    {
        if ($this->maxLeverage === null) {
            return true;
        }
        return $leverage <= $this->maxLeverage;
    }

    private function validateLimits(
        ?float $maxPositionSize,
        ?float $maxExposure,
        ?float $maxDailyLoss,
        ?float $maxDrawdown,
        ?float $maxLeverage,
        ?float $maxConcentration,
        ?int $maxTrades
    ): void {
        if ($maxPositionSize !== null && ($maxPositionSize <= 0 || $maxPositionSize > 100)) {
            throw new \InvalidArgumentException('Max position size must be between 0 and 100%');
        }

        if ($maxExposure !== null && ($maxExposure <= 0 || $maxExposure > 1000)) {
            throw new \InvalidArgumentException('Max portfolio exposure must be between 0 and 1000%');
        }

        if ($maxDailyLoss !== null && ($maxDailyLoss <= 0 || $maxDailyLoss > 100)) {
            throw new \InvalidArgumentException('Max daily loss must be between 0 and 100%');
        }

        if ($maxDrawdown !== null && ($maxDrawdown <= 0 || $maxDrawdown > 100)) {
            throw new \InvalidArgumentException('Max drawdown must be between 0 and 100%');
        }

        if ($maxLeverage !== null && ($maxLeverage < 1 || $maxLeverage > 100)) {
            throw new \InvalidArgumentException('Max leverage must be between 1 and 100');
        }

        if ($maxConcentration !== null && ($maxConcentration <= 0 || $maxConcentration > 100)) {
            throw new \InvalidArgumentException('Max concentration must be between 0 and 100%');
        }

        if ($maxTrades !== null && $maxTrades < 1) {
            throw new \InvalidArgumentException('Max trades per day must be at least 1');
        }
    }

    // Getters
    public function getId(): RiskProfileId { return $this->id; }
    public function getUserId(): UserId { return $this->userId; }
    public function getRiskLevel(): RiskLevel { return $this->riskLevel; }
    public function getMaxPositionSizePercent(): ?float { return $this->maxPositionSizePercent; }
    public function getMaxPortfolioExposurePercent(): ?float { return $this->maxPortfolioExposurePercent; }
    public function getMaxDailyLossPercent(): ?float { return $this->maxDailyLossPercent; }
    public function getMaxDrawdownPercent(): ?float { return $this->maxDrawdownPercent; }
    public function getMaxLeverage(): ?float { return $this->maxLeverage; }
    public function getMaxConcentrationPercent(): ?float { return $this->maxConcentrationPercent; }
    public function getMaxTradesPerDay(): ?int { return $this->maxTradesPerDay; }
    public function isRequireApprovalAboveLimit(): bool { return $this->requireApprovalAboveLimit; }
    public function getNotes(): ?string { return $this->notes; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }
}
