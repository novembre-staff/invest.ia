<?php

declare(strict_types=1);

namespace App\Alert\Domain\Service;

use App\Alert\Domain\Event\AlertTriggered;
use App\Alert\Domain\Model\PriceAlert;
use App\Alert\Domain\Repository\PriceAlertRepositoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Service to trigger alerts and dispatch events
 */
final readonly class AlertTriggerService
{
    public function __construct(
        private PriceAlertRepositoryInterface $alertRepository,
        private MessageBusInterface $eventBus,
        private LoggerInterface $logger
    ) {
    }

    public function triggerAlert(
        PriceAlert $alert,
        float $currentValue,
        ?string $customMessage = null
    ): void {
        try {
            $alert->trigger();
            $this->alertRepository->save($alert);

            $message = $customMessage ?? $this->generateDefaultMessage($alert, $currentValue);

            $event = AlertTriggered::now(
                $alert->getId(),
                $alert->getUserId(),
                $alert->getType(),
                $alert->getSymbol(),
                $alert->getCondition()->getTargetValue(),
                $currentValue,
                $message
            );

            $this->eventBus->dispatch($event);

            $this->logger->info('Alert triggered', [
                'alertId' => $alert->getId()->getValue(),
                'userId' => $alert->getUserId()->getValue(),
                'type' => $alert->getType()->value,
                'symbol' => $alert->getSymbol(),
                'currentValue' => $currentValue,
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to trigger alert', [
                'alertId' => $alert->getId()->getValue(),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    private function generateDefaultMessage(PriceAlert $alert, float $currentValue): string
    {
        $symbol = $alert->getSymbol() ?? 'Portfolio';
        $targetValue = $alert->getCondition()->getTargetValue();

        return match($alert->getType()) {
            AlertType::PRICE_ABOVE => sprintf(
                '%s has reached $%.2f (target: $%.2f)',
                $symbol,
                $currentValue,
                $targetValue
            ),
            AlertType::PRICE_BELOW => sprintf(
                '%s has dropped to $%.2f (target: $%.2f)',
                $symbol,
                $currentValue,
                $targetValue
            ),
            AlertType::PRICE_CHANGE_PERCENT => sprintf(
                '%s has changed by %.2f%% (target: %.2f%%)',
                $symbol,
                (($currentValue - $targetValue) / $targetValue) * 100,
                $targetValue
            ),
            AlertType::VOLUME_SPIKE => sprintf(
                '%s volume has spiked (%.2fx above average)',
                $symbol,
                $currentValue
            ),
            AlertType::PORTFOLIO_VALUE => sprintf(
                'Portfolio value reached $%.2f (target: $%.2f)',
                $currentValue,
                $targetValue
            ),
            AlertType::POSITION_PROFIT_TARGET => sprintf(
                '%s position reached profit target at $%.2f',
                $symbol,
                $currentValue
            ),
            AlertType::POSITION_STOP_LOSS => sprintf(
                '%s position hit stop loss at $%.2f',
                $symbol,
                $currentValue
            ),
        };
    }
}
