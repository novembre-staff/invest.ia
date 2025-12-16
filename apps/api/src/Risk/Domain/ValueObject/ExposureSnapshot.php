<?php

declare(strict_types=1);

namespace App\Risk\Domain\ValueObject;

final readonly class ExposureSnapshot
{
    public function __construct(
        public float $totalExposure,
        public float $longExposure,
        public float $shortExposure,
        public float $netExposure,
        public array $assetExposures, // ['BTCUSDT' => 25.5, 'ETHUSDT' => 15.3]
        public float $leverage,
        public \DateTimeImmutable $timestamp
    ) {
    }

    public function getMaxConcentration(): float
    {
        if (empty($this->assetExposures)) {
            return 0.0;
        }
        return max($this->assetExposures);
    }

    public function getAssetExposure(string $symbol): float
    {
        return $this->assetExposures[$symbol] ?? 0.0;
    }
}
