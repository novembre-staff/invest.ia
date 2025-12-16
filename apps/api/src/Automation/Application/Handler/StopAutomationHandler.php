<?php

declare(strict_types=1);

namespace App\Automation\Application\Handler;

use App\Automation\Application\Command\StopAutomation;
use App\Automation\Application\DTO\AutomationDTO;
use App\Automation\Domain\Repository\AutomationRepositoryInterface;
use App\Automation\Domain\ValueObject\AutomationId;

class StopAutomationHandler
{
    public function __construct(
        private AutomationRepositoryInterface $automationRepository
    ) {
    }

    public function __invoke(StopAutomation $command): AutomationDTO
    {
        $automationId = AutomationId::fromString($command->automationId);
        $automation = $this->automationRepository->findById($automationId);

        if ($automation === null) {
            throw new \DomainException('Automation not found');
        }

        // Verify ownership
        if ($automation->getUserId()->toString() !== $command->userId) {
            throw new \DomainException('Access denied');
        }

        $automation->stop();
        $this->automationRepository->save($automation);

        return AutomationDTO::fromDomain($automation);
    }
}
