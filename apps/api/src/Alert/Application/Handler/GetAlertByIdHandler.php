<?php

declare(strict_types=1);

namespace App\Alert\Application\Handler;

use App\Alert\Application\DTO\PriceAlertDTO;
use App\Alert\Application\Query\GetAlertById;
use App\Alert\Domain\Repository\PriceAlertRepositoryInterface;
use App\Alert\Domain\ValueObject\PriceAlertId;
use App\Identity\Domain\ValueObject\UserId;

final readonly class GetAlertByIdHandler
{
    public function __construct(
        private PriceAlertRepositoryInterface $alertRepository
    ) {
    }

    public function __invoke(GetAlertById $query): PriceAlertDTO
    {
        $alertId = PriceAlertId::fromString($query->alertId);
        $userId = UserId::fromString($query->userId);

        $alert = $this->alertRepository->findById($alertId);

        if ($alert === null) {
            throw new \DomainException('Alert not found');
        }

        // Verify ownership
        if (!$alert->getUserId()->equals($userId)) {
            throw new \DomainException('You do not have permission to view this alert');
        }

        return PriceAlertDTO::fromDomain($alert);
    }
}
