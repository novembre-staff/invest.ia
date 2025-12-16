<?php

declare(strict_types=1);

namespace App\Automation\Application\Handler;

use App\Automation\Application\Command\ActivateAutomation;
use App\Automation\Application\DTO\AutomationDTO;
use App\Automation\Domain\Event\AutomationActivated;
use App\Automation\Domain\Repository\AutomationRepositoryInterface;
use App\Automation\Domain\ValueObject\AutomationId;
use App\Identity\Domain\ValueObject\UserId;
use Symfony\Component\Messenger\MessageBusInterface;

class ActivateAutomationHandler
{
    public function __construct(
        private AutomationRepositoryInterface $automationRepository,
        private MessageBusInterface $eventBus
    ) {
    }

    public function __invoke(ActivateAutomation $command): AutomationDTO
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

        $automation->activate();
        $this->automationRepository->save($automation);

        $this->eventBus->dispatch(
            AutomationActivated::now(
                automationId: $automation->getId()->toString(),
                userId: $command->userId,
                nextExecutionAt: $automation->getNextExecutionAt()
            )
        );

        return AutomationDTO::fromDomain($automation);
    }
}
