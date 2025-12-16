<?php

declare(strict_types=1);

namespace App\Alert\Application\Handler;

use App\Alert\Application\DTO\PriceAlertDTO;
use App\Alert\Application\Query\GetUserAlerts;
use App\Alert\Domain\Repository\PriceAlertRepositoryInterface;
use App\Identity\Domain\ValueObject\UserId;

final readonly class GetUserAlertsHandler
{
    public function __construct(
        private PriceAlertRepositoryInterface $alertRepository
    ) {
    }

    /**
     * @return PriceAlertDTO[]
     */
    public function __invoke(GetUserAlerts $query): array
    {
        $userId = UserId::fromString($query->userId);

        $alerts = $query->activeOnly
            ? $this->alertRepository->findActiveByUserId($userId)
            : $this->alertRepository->findByUserId($userId);

        return array_map(
            fn($alert) => PriceAlertDTO::fromDomain($alert),
            $alerts
        );
    }
}
