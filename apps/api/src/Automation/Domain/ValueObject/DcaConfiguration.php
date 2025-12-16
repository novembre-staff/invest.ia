<?php

declare(strict_types=1);

namespace App\Automation\Domain\ValueObject;

final readonly class DcaConfiguration
{
    public function __construct(
        private float $amountPerPurchase,
        private ExecutionInterval $interval,
        private ?float $maxTotalInvestment = null,
        private ?int $maxExecutions = null,
        private ?\DateTimeImmutable $endDate = null
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if ($this->amountPerPurchase <= 0) {
            throw new \InvalidArgumentException('Amount per purchase must be positive');
        }

        if ($this->maxTotalInvestment !== null && $this->maxTotalInvestment <= 0) {
            throw new \InvalidArgumentException('Max total investment must be positive');
        }

        if ($this->maxExecutions !== null && $this->maxExecutions < 1) {
            throw new \InvalidArgumentException('Max executions must be at least 1');
        }

        if ($this->maxTotalInvestment !== null && $this->maxTotalInvestment < $this->amountPerPurchase) {
            throw new \InvalidArgumentException('Max total investment must be at least equal to amount per purchase');
        }
    }

    public function getAmountPerPurchase(): float
    {
        return $this->amountPerPurchase;
    }

    public function getInterval(): ExecutionInterval
    {
        return $this->interval;
    }

    public function getMaxTotalInvestment(): ?float
    {
        return $this->maxTotalInvestment;
    }

    public function getMaxExecutions(): ?int
    {
        return $this->maxExecutions;
    }

    public function getEndDate(): ?\DateTimeImmutable
    {
        return $this->endDate;
    }

    public function hasReachedInvestmentLimit(float $currentTotalInvested): bool
    {
        if ($this->maxTotalInvestment === null) {
            return false;
        }

        return $currentTotalInvested >= $this->maxTotalInvestment;
    }

    public function hasReachedExecutionLimit(int $currentExecutions): bool
    {
        if ($this->maxExecutions === null) {
            return false;
        }

        return $currentExecutions >= $this->maxExecutions;
    }

    public function hasReachedEndDate(\DateTimeImmutable $now): bool
    {
        if ($this->endDate === null) {
            return false;
        }

        return $now >= $this->endDate;
    }

    public function shouldStop(float $currentTotalInvested, int $currentExecutions, \DateTimeImmutable $now): bool
    {
        return $this->hasReachedInvestmentLimit($currentTotalInvested)
            || $this->hasReachedExecutionLimit($currentExecutions)
            || $this->hasReachedEndDate($now);
    }
}
