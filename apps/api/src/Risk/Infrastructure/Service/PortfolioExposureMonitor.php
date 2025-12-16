<?php

declare(strict_types=1);

namespace App\Risk\Infrastructure\Service;

use App\Identity\Domain\ValueObject\UserId;
use App\Portfolio\Infrastructure\Adapter\PortfolioProviderInterface;
use App\Risk\Domain\Repository\RiskProfileRepositoryInterface;
use App\Risk\Domain\Service\ExposureMonitorInterface;
use App\Risk\Domain\ValueObject\ExposureSnapshot;

class PortfolioExposureMonitor implements ExposureMonitorInterface
{
    public function __construct(
        private readonly PortfolioProviderInterface $portfolioProvider,
        private readonly RiskProfileRepositoryInterface $riskProfileRepository
    ) {
    }

    public function getCurrentExposure(UserId $userId): ExposureSnapshot
    {
        $portfolio = $this->portfolioProvider->getPortfolio($userId->getValue());
        $balances = $portfolio['balances'] ?? [];
        $totalValue = $portfolio['totalValueUSDT'] ?? 0;

        $longExposure = 0;
        $shortExposure = 0;
        $assetExposures = [];

        foreach ($balances as $balance) {
            $asset = $balance['asset'];
            $valueUSDT = $balance['valueUSDT'] ?? 0;
            $exposurePercent = $totalValue > 0 ? ($valueUSDT / $totalValue) * 100 : 0;

            if ($exposurePercent > 0) {
                $longExposure += $exposurePercent;
                $assetExposures[$asset] = $exposurePercent;
            } else {
                $shortExposure += abs($exposurePercent);
            }
        }

        $totalExposure = $longExposure + $shortExposure;
        $netExposure = $longExposure - $shortExposure;
        $leverage = $totalValue > 0 ? $totalExposure / 100 : 1.0;

        return new ExposureSnapshot(
            totalExposure: $totalExposure,
            longExposure: $longExposure,
            shortExposure: $shortExposure,
            netExposure: $netExposure,
            assetExposures: $assetExposures,
            leverage: $leverage,
            timestamp: new \DateTimeImmutable()
        );
    }

    public function wouldExceedLimit(
        UserId $userId,
        string $symbol,
        float $positionSizePercent
    ): bool {
        $profile = $this->riskProfileRepository->findByUserId($userId);
        if (!$profile) {
            return false; // No profile = no limits
        }

        // Check position size limit
        if (!$profile->isPositionSizeAllowed($positionSizePercent)) {
            return true;
        }

        // Check concentration limit
        $currentExposure = $this->getCurrentExposure($userId);
        $currentSymbolExposure = $currentExposure->getAssetExposure($symbol);
        $newSymbolExposure = $currentSymbolExposure + $positionSizePercent;

        $maxConcentration = $profile->getMaxConcentrationPercent();
        if ($maxConcentration && $newSymbolExposure > $maxConcentration) {
            return true;
        }

        // Check total exposure limit
        $newTotalExposure = $currentExposure->totalExposure + $positionSizePercent;
        $maxExposure = $profile->getMaxPortfolioExposurePercent();
        if ($maxExposure && $newTotalExposure > $maxExposure) {
            return true;
        }

        return false;
    }

    public function getAvailableCapacity(UserId $userId): float
    {
        $profile = $this->riskProfileRepository->findByUserId($userId);
        if (!$profile) {
            return 100.0; // No profile = unlimited
        }

        $currentExposure = $this->getCurrentExposure($userId);
        $maxExposure = $profile->getMaxPortfolioExposurePercent() ?? 100.0;

        return max(0, $maxExposure - $currentExposure->totalExposure);
    }
}
