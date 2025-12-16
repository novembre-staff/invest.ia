<?php

declare(strict_types=1);

namespace App\Automation\Application\Handler;

use App\Automation\Application\DTO\AutomationDTO;
use App\Automation\Application\Query\GetAutomation;
use App\Automation\Domain\Repository\AutomationRepositoryInterface;
use App\Automation\Domain\ValueObject\AutomationId;

class GetAutomationHandler
{
    public function __construct(
        private AutomationRepositoryInterface $automationRepository
    ) {
    }

    public function __invoke(GetAutomation $query): ?AutomationDTO
    {
        $automationId = AutomationId::fromString($query->automationId);
        $automation = $this->automationRepository->findById($automationId);

        if ($automation === null) {
            return null;
        }

        // Verify ownership
        if ($automation->getUserId()->toString() !== $query->userId) {
            throw new \DomainException('Access denied');
        }

        return AutomationDTO::fromDomain($automation);
    }
}
