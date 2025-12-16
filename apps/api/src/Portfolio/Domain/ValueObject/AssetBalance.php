<?php

declare(strict_types=1);

namespace App\Portfolio\Domain\ValueObject;

/**
 * Asset balance in portfolio
 */
final readonly class AssetBalance
{
    public function __construct(
        private string $asset,
        private float $free,
        private float $locked
    ) {
        if (empty($asset)) {
            throw new \InvalidArgumentException('Asset cannot be empty');
        }

        if ($free < 0 || $locked < 0) {
            throw new \InvalidArgumentException('Balance cannot be negative');
        }
    }

    public function getAsset(): string
    {
        return $this->asset;
    }

    public function getFree(): float
    {
        return $this->free;
    }

    public function getLocked(): float
    {
        return $this->locked;
    }

    public function getTotal(): float
    {
        return $this->free + $this->locked;
    }

    public function hasBalance(): bool
    {
        return $this->getTotal() > 0;
    }
}
