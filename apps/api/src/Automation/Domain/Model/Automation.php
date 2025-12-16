<?php

declare(strict_types=1);

namespace App\Automation\Domain\Model;

use App\Automation\Domain\ValueObject\AutomationId;
use App\Automation\Domain\ValueObject\AutomationStatus;
use App\Automation\Domain\ValueObject\AutomationType;
use App\Automation\Domain\ValueObject\DcaConfiguration;
use App\Automation\Domain\ValueObject\ExecutionInterval;
use App\Automation\Domain\ValueObject\GridConfiguration;
use App\Identity\Domain\ValueObject\UserId;
use App\Market\Domain\ValueObject\Symbol;

class Automation
{
    private AutomationId $id;
    private UserId $userId;
    private string $name;
    private AutomationType $type;
    private AutomationStatus $status;
    private Symbol $symbol;
    private ?ExecutionInterval $interval = null;
    private ?DcaConfiguration $dcaConfig = null;
    private ?GridConfiguration $gridConfig = null;
    private array $parameters = [];
    private int $executionCount = 0;
    private float $totalInvested = 0.0;
    private float $totalProfit = 0.0;
    private ?\DateTimeImmutable $lastExecutedAt = null;
    private ?\DateTimeImmutable $nextExecutionAt = null;
    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;

    private function __construct(
        AutomationId $id,
        UserId $userId,
        string $name,
        AutomationType $type,
        Symbol $symbol,
        ?ExecutionInterval $interval = null,
        ?DcaConfiguration $dcaConfig = null,
        ?GridConfiguration $gridConfig = null,
        array $parameters = []
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->name = $name;
        $this->type = $type;
        $this->symbol = $symbol;
        $this->interval = $interval;
        $this->dcaConfig = $dcaConfig;
        $this->gridConfig = $gridConfig;
        $this->parameters = $parameters;
        $this->status = AutomationStatus::DRAFT;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();

        $this->validate();
    }

    public static function create(
        UserId $userId,
        string $name,
        AutomationType $type,
        Symbol $symbol,
        ?ExecutionInterval $interval = null,
        ?DcaConfiguration $dcaConfig = null,
        ?GridConfiguration $gridConfig = null,
        array $parameters = []
    ): self {
        return new self(
            AutomationId::generate(),
            $userId,
            $name,
            $type,
            $symbol,
            $interval,
            $dcaConfig,
            $gridConfig,
            $parameters
        );
    }

    private function validate(): void
    {
        if (empty($this->name)) {
            throw new \InvalidArgumentException('Automation name cannot be empty');
        }

        // DCA requires interval and DCA config
        if ($this->type === AutomationType::DCA) {
            if ($this->interval === null) {
                throw new \InvalidArgumentException('DCA automation requires an execution interval');
            }
            if ($this->dcaConfig === null) {
                throw new \InvalidArgumentException('DCA automation requires a DCA configuration');
            }
        }

        // Grid trading requires grid config
        if ($this->type === AutomationType::GRID_TRADING) {
            if ($this->gridConfig === null) {
                throw new \InvalidArgumentException('Grid trading automation requires a grid configuration');
            }
        }

        // Rebalancing requires interval
        if ($this->type === AutomationType::REBALANCING) {
            if ($this->interval === null) {
                throw new \InvalidArgumentException('Rebalancing automation requires an execution interval');
            }
        }
    }

    public function activate(): void
    {
        if (!$this->status->canBeActivated()) {
            throw new \DomainException("Cannot activate automation with status {$this->status->value}");
        }

        $this->status = AutomationStatus::ACTIVE;
        $this->calculateNextExecutionTime();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function pause(): void
    {
        if (!$this->status->canBePaused()) {
            throw new \DomainException("Cannot pause automation with status {$this->status->value}");
        }

        $this->status = AutomationStatus::PAUSED;
        $this->nextExecutionAt = null;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function stop(): void
    {
        if (!$this->status->canBeStopped()) {
            throw new \DomainException("Cannot stop automation with status {$this->status->value}");
        }

        $this->status = AutomationStatus::STOPPED;
        $this->nextExecutionAt = null;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function markAsCompleted(): void
    {
        $this->status = AutomationStatus::COMPLETED;
        $this->nextExecutionAt = null;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function markAsFailed(): void
    {
        $this->status = AutomationStatus::FAILED;
        $this->nextExecutionAt = null;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function recordExecution(float $investedAmount, float $profit = 0.0): void
    {
        $this->executionCount++;
        $this->totalInvested += $investedAmount;
        $this->totalProfit += $profit;
        $this->lastExecutedAt = new \DateTimeImmutable();
        $this->calculateNextExecutionTime();
        $this->updatedAt = new \DateTimeImmutable();

        // Check if automation should complete automatically
        if ($this->dcaConfig !== null) {
            $now = new \DateTimeImmutable();
            if ($this->dcaConfig->shouldStop($this->totalInvested, $this->executionCount, $now)) {
                $this->markAsCompleted();
            }
        }
    }

    public function updateConfiguration(
        string $name,
        ?ExecutionInterval $interval = null,
        ?DcaConfiguration $dcaConfig = null,
        ?GridConfiguration $gridConfig = null,
        array $parameters = []
    ): void {
        if ($this->status === AutomationStatus::ACTIVE) {
            throw new \DomainException('Cannot update configuration while automation is active');
        }

        $this->name = $name;
        $this->interval = $interval;
        $this->dcaConfig = $dcaConfig;
        $this->gridConfig = $gridConfig;
        $this->parameters = $parameters;
        $this->updatedAt = new \DateTimeImmutable();

        $this->validate();
    }

    private function calculateNextExecutionTime(): void
    {
        if ($this->status !== AutomationStatus::ACTIVE || $this->interval === null) {
            $this->nextExecutionAt = null;
            return;
        }

        $minutes = $this->interval->getMinutes();
        $this->nextExecutionAt = (new \DateTimeImmutable())->modify("+{$minutes} minutes");
    }

    public function shouldExecuteNow(\DateTimeImmutable $now): bool
    {
        if ($this->status !== AutomationStatus::ACTIVE) {
            return false;
        }

        if ($this->nextExecutionAt === null) {
            return false;
        }

        return $now >= $this->nextExecutionAt;
    }

    // Getters
    public function getId(): AutomationId
    {
        return $this->id;
    }

    public function getUserId(): UserId
    {
        return $this->userId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): AutomationType
    {
        return $this->type;
    }

    public function getStatus(): AutomationStatus
    {
        return $this->status;
    }

    public function getSymbol(): Symbol
    {
        return $this->symbol;
    }

    public function getInterval(): ?ExecutionInterval
    {
        return $this->interval;
    }

    public function getDcaConfig(): ?DcaConfiguration
    {
        return $this->dcaConfig;
    }

    public function getGridConfig(): ?GridConfiguration
    {
        return $this->gridConfig;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getExecutionCount(): int
    {
        return $this->executionCount;
    }

    public function getTotalInvested(): float
    {
        return $this->totalInvested;
    }

    public function getTotalProfit(): float
    {
        return $this->totalProfit;
    }

    public function getLastExecutedAt(): ?\DateTimeImmutable
    {
        return $this->lastExecutedAt;
    }

    public function getNextExecutionAt(): ?\DateTimeImmutable
    {
        return $this->nextExecutionAt;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getAverageInvestedPerExecution(): float
    {
        if ($this->executionCount === 0) {
            return 0.0;
        }

        return $this->totalInvested / $this->executionCount;
    }

    public function getReturnOnInvestment(): float
    {
        if ($this->totalInvested === 0) {
            return 0.0;
        }

        return ($this->totalProfit / $this->totalInvested) * 100;
    }
}
