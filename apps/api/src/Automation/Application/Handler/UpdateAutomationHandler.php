<?php

declare(strict_types=1);

namespace App\Automation\Application\Handler;

use App\Automation\Application\Command\UpdateAutomation;
use App\Automation\Application\DTO\AutomationDTO;
use App\Automation\Domain\Repository\AutomationRepositoryInterface;
use App\Automation\Domain\ValueObject\AutomationId;
use App\Automation\Domain\ValueObject\DcaConfiguration;
use App\Automation\Domain\ValueObject\ExecutionInterval;
use App\Automation\Domain\ValueObject\GridConfiguration;

class UpdateAutomationHandler
{
    public function __construct(
        private AutomationRepositoryInterface $automationRepository
    ) {
    }

    public function __invoke(UpdateAutomation $command): AutomationDTO
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

        $interval = $command->interval !== null 
            ? ExecutionInterval::from($command->interval) 
            : null;

        $dcaConfig = null;
        if ($command->dcaConfig !== null) {
            $dcaConfig = new DcaConfiguration(
                amountPerPurchase: $command->dcaConfig['amount_per_purchase'],
                interval: ExecutionInterval::from($command->dcaConfig['interval']),
                maxTotalInvestment: $command->dcaConfig['max_total_investment'] ?? null,
                maxExecutions: $command->dcaConfig['max_executions'] ?? null,
                endDate: isset($command->dcaConfig['end_date']) 
                    ? new \DateTimeImmutable($command->dcaConfig['end_date'])
                    : null
            );
        }

        $gridConfig = null;
        if ($command->gridConfig !== null) {
            $gridConfig = new GridConfiguration(
                lowerPrice: $command->gridConfig['lower_price'],
                upperPrice: $command->gridConfig['upper_price'],
                gridLevels: $command->gridConfig['grid_levels'],
                quantityPerGrid: $command->gridConfig['quantity_per_grid'],
                isArithmetic: $command->gridConfig['is_arithmetic'] ?? true
            );
        }

        $automation->updateConfiguration(
            name: $command->name,
            interval: $interval,
            dcaConfig: $dcaConfig,
            gridConfig: $gridConfig,
            parameters: $command->parameters
        );

        $this->automationRepository->save($automation);

        return AutomationDTO::fromDomain($automation);
    }
}
