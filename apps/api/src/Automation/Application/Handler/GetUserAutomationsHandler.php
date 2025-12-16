<?php

declare(strict_types=1);

namespace App\Automation\Application\Handler;

use App\Automation\Application\DTO\AutomationDTO;
use App\Automation\Application\Query\GetUserAutomations;
use App\Automation\Domain\Repository\AutomationRepositoryInterface;
use App\Identity\Domain\ValueObject\UserId;

class GetUserAutomationsHandler
{
    public function __construct(
        private AutomationRepositoryInterface $automationRepository
    ) {
    }

    public function __invoke(GetUserAutomations $query): array
    {
        $userId = UserId::fromString($query->userId);
        $automations = $this->automationRepository->findByUserId($userId);

        return array_map(
            fn($automation) => AutomationDTO::fromDomain($automation),
            $automations
        );
    }
}
