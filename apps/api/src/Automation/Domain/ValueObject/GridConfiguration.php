<?php

declare(strict_types=1);

namespace App\Automation\Domain\ValueObject;

final readonly class GridConfiguration
{
    public function __construct(
        private float $lowerPrice,
        private float $upperPrice,
        private int $gridLevels,
        private float $quantityPerGrid,
        private bool $isArithmetic = true // true = arithmetic, false = geometric
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if ($this->lowerPrice <= 0) {
            throw new \InvalidArgumentException('Lower price must be positive');
        }

        if ($this->upperPrice <= $this->lowerPrice) {
            throw new \InvalidArgumentException('Upper price must be greater than lower price');
        }

        if ($this->gridLevels < 2) {
            throw new \InvalidArgumentException('Grid must have at least 2 levels');
        }

        if ($this->gridLevels > 100) {
            throw new \InvalidArgumentException('Grid cannot have more than 100 levels');
        }

        if ($this->quantityPerGrid <= 0) {
            throw new \InvalidArgumentException('Quantity per grid must be positive');
        }
    }

    public function getLowerPrice(): float
    {
        return $this->lowerPrice;
    }

    public function getUpperPrice(): float
    {
        return $this->upperPrice;
    }

    public function getGridLevels(): int
    {
        return $this->gridLevels;
    }

    public function getQuantityPerGrid(): float
    {
        return $this->quantityPerGrid;
    }

    public function isArithmetic(): bool
    {
        return $this->isArithmetic;
    }

    public function calculateGridPrices(): array
    {
        $prices = [];
        
        if ($this->isArithmetic) {
            // Arithmetic progression: equal spacing
            $step = ($this->upperPrice - $this->lowerPrice) / ($this->gridLevels - 1);
            for ($i = 0; $i < $this->gridLevels; $i++) {
                $prices[] = $this->lowerPrice + ($step * $i);
            }
        } else {
            // Geometric progression: exponential spacing
            $ratio = pow($this->upperPrice / $this->lowerPrice, 1 / ($this->gridLevels - 1));
            for ($i = 0; $i < $this->gridLevels; $i++) {
                $prices[] = $this->lowerPrice * pow($ratio, $i);
            }
        }

        return $prices;
    }

    public function getTotalInvestment(): float
    {
        return $this->quantityPerGrid * $this->gridLevels;
    }
}
