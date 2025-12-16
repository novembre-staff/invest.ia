<?php

declare(strict_types=1);

namespace App\Risk\Application\Handler;

use App\Identity\Domain\ValueObject\UserId;
use App\Risk\Application\Query\GetCurrentExposure;
use App\Risk\Domain\Service\ExposureMonitorInterface;

final readonly class GetCurrentExposureHandler
{
    public function __construct(
        private ExposureMonitorInterface $exposureMonitor
    ) {
    }

    public function __invoke(GetCurrentExposure $query): array
    {
        $exposure = $this->exposureMonitor->getCurrentExposure(
            UserId::fromString($query->userId)
        );

        return [
            'totalExposure' => $exposure->totalExposure,
            'longExposure' => $exposure->longExposure,
            'shortExposure' => $exposure->shortExposure,
            'netExposure' => $exposure->netExposure,
            'assetExposures' => $exposure->assetExposures,
            'leverage' => $exposure->leverage,
            'maxConcentration' => $exposure->getMaxConcentration(),
            'timestamp' => $exposure->timestamp->format('c'),
        ];
    }
}
