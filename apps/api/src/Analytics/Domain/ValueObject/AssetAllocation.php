<?php

declare(strict_types=1);

namespace App\Analytics\Domain\ValueObject;

final readonly class AssetAllocation
{
    /**
     * @param array<string, float> $allocations Symbol => Percentage
     */
    public function __construct(
        private array $allocations,
        private float $totalValue
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        $totalPercent = array_sum($this->allocations);
        
        // Allow small rounding errors
        if (abs($totalPercent - 100.0) > 0.01) {
            throw new \InvalidArgumentException(
                "Asset allocations must sum to 100%, got {$totalPercent}%"
            );
        }

        foreach ($this->allocations as $symbol => $percent) {
            if ($percent < 0 || $percent > 100) {
                throw new \InvalidArgumentException(
                    "Allocation for {$symbol} must be between 0 and 100%, got {$percent}%"
                );
            }
        }
    }

    public function getAllocations(): array
    {
        return $this->allocations;
    }

    public function getTotalValue(): float
    {
        return $this->totalValue;
    }

    public function getAllocation(string $symbol): float
    {
        return $this->allocations[$symbol] ?? 0.0;
    }

    public function getValue(string $symbol): float
    {
        return $this->totalValue * ($this->getAllocation($symbol) / 100);
    }

    public function getTopAssets(int $limit = 5): array
    {
        $sorted = $this->allocations;
        arsort($sorted);
        return array_slice($sorted, 0, $limit, true);
    }

    public function getDiversificationScore(): float
    {
        // Herfindahl-Hirschman Index (HHI)
        // Lower values = better diversification
        $hhi = 0.0;
        foreach ($this->allocations as $percent) {
            $hhi += pow($percent, 2);
        }
        
        // Convert to 0-100 scale where 100 = perfectly diversified
        $maxHHI = 10000; // If 100% in one asset
        $minHHI = 10000 / count($this->allocations); // Perfectly distributed
        
        if ($maxHHI === $minHHI) {
            return 100.0;
        }
        
        return max(0, min(100, 100 * (1 - (($hhi - $minHHI) / ($maxHHI - $minHHI)))));
    }
}
