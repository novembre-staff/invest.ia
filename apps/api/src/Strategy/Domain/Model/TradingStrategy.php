<?php

declare(strict_types=1);

namespace App\Strategy\Domain\Model;

use App\Identity\Domain\ValueObject\UserId;
use App\Strategy\Domain\ValueObject\Indicator;
use App\Strategy\Domain\ValueObject\StrategyId;
use App\Strategy\Domain\ValueObject\StrategyStatus;
use App\Strategy\Domain\ValueObject\StrategyType;
use App\Strategy\Domain\ValueObject\TimeFrame;

class TradingStrategy
{
    private StrategyId $id;
    private UserId $userId;
    private string $name;
    private ?string $description;
    private StrategyType $type;
    private StrategyStatus $status;
    private array $symbols; // ['BTCUSDT', 'ETHUSDT']
    private TimeFrame $timeFrame;
    private array $indicators; // [['indicator' => Indicator::RSI, 'parameters' => ['period' => 14]]]
    private array $entryRules; // JSON rules for entry
    private array $exitRules; // JSON rules for exit
    private ?float $positionSizePercent; // % of portfolio per trade
    private ?float $maxDrawdownPercent; // Max allowed drawdown
    private ?float $stopLossPercent;
    private ?float $takeProfitPercent;
    private ?array $backtestResults; // Latest backtest results
    private ?\DateTimeImmutable $lastBacktestedAt;
    private ?\DateTimeImmutable $activatedAt;
    private ?\DateTimeImmutable $stoppedAt;
    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;

    private function __construct(
        StrategyId $id,
        UserId $userId,
        string $name,
        ?string $description,
        StrategyType $type,
        array $symbols,
        TimeFrame $timeFrame,
        array $indicators,
        array $entryRules,
        array $exitRules,
        ?float $positionSizePercent = 10.0,
        ?float $maxDrawdownPercent = 20.0,
        ?float $stopLossPercent = null,
        ?float $takeProfitPercent = null
    ) {
        $this->validateName($name);
        $this->validateSymbols($symbols);
        $this->validateIndicators($indicators);
        $this->validateRules($entryRules, 'entry');
        $this->validateRules($exitRules, 'exit');
        $this->validatePercentages($positionSizePercent, $maxDrawdownPercent, $stopLossPercent, $takeProfitPercent);

        $this->id = $id;
        $this->userId = $userId;
        $this->name = $name;
        $this->description = $description;
        $this->type = $type;
        $this->status = StrategyStatus::DRAFT;
        $this->symbols = $symbols;
        $this->timeFrame = $timeFrame;
        $this->indicators = $indicators;
        $this->entryRules = $entryRules;
        $this->exitRules = $exitRules;
        $this->positionSizePercent = $positionSizePercent;
        $this->maxDrawdownPercent = $maxDrawdownPercent;
        $this->stopLossPercent = $stopLossPercent;
        $this->takeProfitPercent = $takeProfitPercent;
        $this->backtestResults = null;
        $this->lastBacktestedAt = null;
        $this->activatedAt = null;
        $this->stoppedAt = null;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public static function create(
        UserId $userId,
        string $name,
        ?string $description,
        StrategyType $type,
        array $symbols,
        TimeFrame $timeFrame,
        array $indicators,
        array $entryRules,
        array $exitRules,
        ?float $positionSizePercent = 10.0,
        ?float $maxDrawdownPercent = 20.0,
        ?float $stopLossPercent = null,
        ?float $takeProfitPercent = null
    ): self {
        return new self(
            StrategyId::generate(),
            $userId,
            $name,
            $description,
            $type,
            $symbols,
            $timeFrame,
            $indicators,
            $entryRules,
            $exitRules,
            $positionSizePercent,
            $maxDrawdownPercent,
            $stopLossPercent,
            $takeProfitPercent
        );
    }

    public function updateConfiguration(
        string $name,
        ?string $description,
        array $symbols,
        TimeFrame $timeFrame,
        array $indicators,
        array $entryRules,
        array $exitRules,
        ?float $positionSizePercent,
        ?float $maxDrawdownPercent,
        ?float $stopLossPercent,
        ?float $takeProfitPercent
    ): void {
        if ($this->status === StrategyStatus::ACTIVE) {
            throw new \DomainException('Cannot update configuration of active strategy');
        }

        $this->validateName($name);
        $this->validateSymbols($symbols);
        $this->validateIndicators($indicators);
        $this->validateRules($entryRules, 'entry');
        $this->validateRules($exitRules, 'exit');
        $this->validatePercentages($positionSizePercent, $maxDrawdownPercent, $stopLossPercent, $takeProfitPercent);

        $this->name = $name;
        $this->description = $description;
        $this->symbols = $symbols;
        $this->timeFrame = $timeFrame;
        $this->indicators = $indicators;
        $this->entryRules = $entryRules;
        $this->exitRules = $exitRules;
        $this->positionSizePercent = $positionSizePercent;
        $this->maxDrawdownPercent = $maxDrawdownPercent;
        $this->stopLossPercent = $stopLossPercent;
        $this->takeProfitPercent = $takeProfitPercent;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function startBacktest(): void
    {
        if (!$this->status->canBeBacktested()) {
            throw new \DomainException("Strategy in status {$this->status->value} cannot be backtested");
        }

        $this->status = StrategyStatus::BACKTESTING;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function completeBacktest(array $results): void
    {
        if ($this->status !== StrategyStatus::BACKTESTING) {
            throw new \DomainException('Strategy is not in backtesting status');
        }

        $this->backtestResults = $results;
        $this->lastBacktestedAt = new \DateTimeImmutable();

        // Check if backtest passed minimum criteria
        $profitability = $results['profitability'] ?? 0;
        $winRate = $results['winRate'] ?? 0;
        $maxDrawdown = $results['maxDrawdown'] ?? 100;

        if ($profitability > 0 && $winRate >= 40 && $maxDrawdown <= ($this->maxDrawdownPercent ?? 20)) {
            $this->status = StrategyStatus::BACKTEST_PASSED;
        } else {
            $this->status = StrategyStatus::BACKTEST_FAILED;
        }

        $this->updatedAt = new \DateTimeImmutable();
    }

    public function activate(): void
    {
        if (!$this->status->canBeActivated()) {
            throw new \DomainException("Strategy in status {$this->status->value} cannot be activated");
        }

        $this->status = StrategyStatus::ACTIVE;
        $this->activatedAt = new \DateTimeImmutable();
        $this->stoppedAt = null;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function pause(): void
    {
        if (!$this->status->canBePaused()) {
            throw new \DomainException("Strategy in status {$this->status->value} cannot be paused");
        }

        $this->status = StrategyStatus::PAUSED;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function stop(): void
    {
        if (!$this->status->canBeStopped()) {
            throw new \DomainException("Strategy in status {$this->status->value} cannot be stopped");
        }

        $this->status = StrategyStatus::STOPPED;
        $this->stoppedAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    private function validateName(string $name): void
    {
        if (strlen(trim($name)) < 3) {
            throw new \InvalidArgumentException('Strategy name must be at least 3 characters');
        }

        if (strlen($name) > 100) {
            throw new \InvalidArgumentException('Strategy name cannot exceed 100 characters');
        }
    }

    private function validateSymbols(array $symbols): void
    {
        if (empty($symbols)) {
            throw new \InvalidArgumentException('Strategy must have at least one symbol');
        }

        foreach ($symbols as $symbol) {
            if (!is_string($symbol) || strlen($symbol) < 3) {
                throw new \InvalidArgumentException('Invalid symbol format');
            }
        }
    }

    private function validateIndicators(array $indicators): void
    {
        foreach ($indicators as $config) {
            if (!isset($config['indicator']) || !($config['indicator'] instanceof Indicator)) {
                throw new \InvalidArgumentException('Each indicator must have an Indicator enum value');
            }

            if (!isset($config['parameters']) || !is_array($config['parameters'])) {
                throw new \InvalidArgumentException('Each indicator must have parameters array');
            }
        }
    }

    private function validateRules(array $rules, string $type): void
    {
        if (empty($rules)) {
            throw new \InvalidArgumentException("Strategy must have at least one {$type} rule");
        }

        // Rules are JSON-like arrays with conditions
        // Example: [['field' => 'RSI', 'operator' => '<', 'value' => 30]]
        foreach ($rules as $rule) {
            if (!is_array($rule) || !isset($rule['field'], $rule['operator'], $rule['value'])) {
                throw new \InvalidArgumentException("Invalid {$type} rule format");
            }
        }
    }

    private function validatePercentages(
        ?float $positionSize,
        ?float $maxDrawdown,
        ?float $stopLoss,
        ?float $takeProfit
    ): void {
        if ($positionSize !== null && ($positionSize <= 0 || $positionSize > 100)) {
            throw new \InvalidArgumentException('Position size must be between 0 and 100%');
        }

        if ($maxDrawdown !== null && ($maxDrawdown <= 0 || $maxDrawdown > 100)) {
            throw new \InvalidArgumentException('Max drawdown must be between 0 and 100%');
        }

        if ($stopLoss !== null && ($stopLoss <= 0 || $stopLoss > 100)) {
            throw new \InvalidArgumentException('Stop loss must be between 0 and 100%');
        }

        if ($takeProfit !== null && ($takeProfit <= 0 || $takeProfit > 1000)) {
            throw new \InvalidArgumentException('Take profit must be between 0 and 1000%');
        }
    }

    // Getters
    public function getId(): StrategyId { return $this->id; }
    public function getUserId(): UserId { return $this->userId; }
    public function getName(): string { return $this->name; }
    public function getDescription(): ?string { return $this->description; }
    public function getType(): StrategyType { return $this->type; }
    public function getStatus(): StrategyStatus { return $this->status; }
    public function getSymbols(): array { return $this->symbols; }
    public function getTimeFrame(): TimeFrame { return $this->timeFrame; }
    public function getIndicators(): array { return $this->indicators; }
    public function getEntryRules(): array { return $this->entryRules; }
    public function getExitRules(): array { return $this->exitRules; }
    public function getPositionSizePercent(): ?float { return $this->positionSizePercent; }
    public function getMaxDrawdownPercent(): ?float { return $this->maxDrawdownPercent; }
    public function getStopLossPercent(): ?float { return $this->stopLossPercent; }
    public function getTakeProfitPercent(): ?float { return $this->takeProfitPercent; }
    public function getBacktestResults(): ?array { return $this->backtestResults; }
    public function getLastBacktestedAt(): ?\DateTimeImmutable { return $this->lastBacktestedAt; }
    public function getActivatedAt(): ?\DateTimeImmutable { return $this->activatedAt; }
    public function getStoppedAt(): ?\DateTimeImmutable { return $this->stoppedAt; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }
}
