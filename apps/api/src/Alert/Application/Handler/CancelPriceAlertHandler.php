<?php

declare(strict_types=1);

namespace App\Alert\Application\Handler;

use App\Alert\Application\Command\CancelPriceAlert;
use App\Alert\Domain\Repository\PriceAlertRepositoryInterface;
use App\Alert\Domain\ValueObject\PriceAlertId;
use App\Identity\Domain\ValueObject\UserId;

final readonly class CancelPriceAlertHandler
{
    public function __construct(
        private PriceAlertRepositoryInterface $alertRepository
    ) {
    }

    public function __invoke(CancelPriceAlert $command): void
    {
        $alertId = PriceAlertId::fromString($command->alertId);
        $userId = UserId::fromString($command->userId);

        $alert = $this->alertRepository->findById($alertId);

        if ($alert === null) {
            throw new \DomainException('Alert not found');
        }

        // Verify ownership
        if (!$alert->getUserId()->equals($userId)) {
            throw new \DomainException('You do not have permission to cancel this alert');
        }

        $alert->cancel();
        $this->alertRepository->save($alert);
    }
}
