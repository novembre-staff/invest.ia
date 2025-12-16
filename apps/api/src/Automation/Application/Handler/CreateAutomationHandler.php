<?php

declare(strict_types=1);

namespace App\Automation\Application\Handler;

use App\Automation\Application\Command\CreateAutomation;
use App\Automation\Application\DTO\AutomationDTO;
use App\Automation\Domain\Event\AutomationCreated;
use App\Automation\Domain\Model\Automation;
use App\Automation\Domain\Repository\AutomationRepositoryInterface;
use App\Automation\Domain\ValueObject\AutomationType;
use App\Automation\Domain\ValueObject\DcaConfiguration;
use App\Automation\Domain\ValueObject\ExecutionInterval;
use App\Automation\Domain\ValueObject\GridConfiguration;
use App\Identity\Domain\ValueObject\UserId;
use App\Market\Domain\ValueObject\Symbol;
use Symfony\Component\Messenger\MessageBusInterface;

class CreateAutomationHandler
{
    public function __construct(
        private AutomationRepositoryInterface $automationRepository,
        private MessageBusInterface $eventBus
    ) {
    }

    public function __invoke(CreateAutomation $command): AutomationDTO
    {
        $userId = UserId::fromString($command->userId);
        $type = AutomationType::from($command->type);
        $symbol = Symbol::fromString($command->symbol);
        
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

        $automation = Automation::create(
            userId: $userId,
            name: $command->name,
            type: $type,
            symbol: $symbol,
            interval: $interval,
            dcaConfig: $dcaConfig,
            gridConfig: $gridConfig,
            parameters: $command->parameters
        );

        $this->automationRepository->save($automation);

        $this->eventBus->dispatch(
            AutomationCreated::now(
                automationId: $automation->getId()->toString(),
                userId: $userId->toString(),
                type: $type->value,
                symbol: $symbol->toString()
            )
        );

        return AutomationDTO::fromDomain($automation);
    }
}
