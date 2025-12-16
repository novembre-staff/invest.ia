<?php

declare(strict_types=1);

namespace App\Bots\Domain\ValueObject;

use InvalidArgumentException;

/**
 * Configuration de rebalancing pour un bot
 */
final class RebalancingConfig
{
    public function __construct(
        private readonly RebalancingStrategy $strategy,
        private readonly ?int $periodDays = null,                    // Pour PERIODIC
        private readonly ?float $thresholdPercent = null,            // Pour THRESHOLD (ex: 5%)
        private readonly ?float $driftPercent = null,                // Pour DRIFT (ex: 10%)
        private readonly bool $autoExecute = false                   // Auto ou demande approbation
    ) {
        $this->validate();
    }

    public static function none(): self
    {
        return new self(
            strategy: RebalancingStrategy::none(),
            autoExecute: false
        );
    }

    public static function periodic(int $days, bool $autoExecute = false): self
    {
        return new self(
            strategy: RebalancingStrategy::periodic(),
            periodDays: $days,
            autoExecute: $autoExecute
        );
    }

    public static function threshold(float $percent, bool $autoExecute = false): self
    {
        return new self(
            strategy: RebalancingStrategy::threshold(),
            thresholdPercent: $percent,
            autoExecute: $autoExecute
        );
    }

    public static function drift(float $percent, bool $autoExecute = false): self
    {
        return new self(
            strategy: RebalancingStrategy::drift(),
            driftPercent: $percent,
            autoExecute: $autoExecute
        );
    }

    public function strategy(): RebalancingStrategy
    {
        return $this->strategy;
    }

    public function periodDays(): ?int
    {
        return $this->periodDays;
    }

    public function thresholdPercent(): ?float
    {
        return $this->thresholdPercent;
    }

    public function driftPercent(): ?float
    {
        return $this->driftPercent;
    }

    public function autoExecute(): bool
    {
        return $this->autoExecute;
    }

    public function isEnabled(): bool
    {
        return $this->strategy->isEnabled();
    }

    private function validate(): void
    {
        switch ($this->strategy->value()) {
            case RebalancingStrategy::PERIODIC:
                if ($this->periodDays === null || $this->periodDays < 1) {
                    throw new InvalidArgumentException('Period days must be >= 1 for PERIODIC strategy');
                }
                break;

            case RebalancingStrategy::THRESHOLD:
                if ($this->thresholdPercent === null || $this->thresholdPercent <= 0 || $this->thresholdPercent > 100) {
                    throw new InvalidArgumentException('Threshold percent must be between 0 and 100');
                }
                break;

            case RebalancingStrategy::DRIFT:
                if ($this->driftPercent === null || $this->driftPercent <= 0 || $this->driftPercent > 100) {
                    throw new InvalidArgumentException('Drift percent must be between 0 and 100');
                }
                break;
        }
    }
}
