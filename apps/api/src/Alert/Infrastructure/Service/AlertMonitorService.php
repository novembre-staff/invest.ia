<?php

declare(strict_types=1);

namespace App\Alert\Infrastructure\Service;

use App\Alert\Domain\Model\PriceAlert;
use App\Alert\Domain\Repository\PriceAlertRepositoryInterface;
use App\Alert\Domain\Service\AlertEvaluatorInterface;
use App\Alert\Domain\Service\AlertTriggerService;
use App\Market\Infrastructure\Adapter\MarketDataProviderInterface;
use Psr\Log\LoggerInterface;

/**
 * Background service to monitor and evaluate active alerts
 * Should be run as a scheduled task (cron/supervisor)
 */
final readonly class AlertMonitorService
{
    /**
     * @param AlertEvaluatorInterface[] $evaluators
     */
    public function __construct(
        private PriceAlertRepositoryInterface $alertRepository,
        private MarketDataProviderInterface $marketDataProvider,
        private AlertTriggerService $triggerService,
        private iterable $evaluators,
        private LoggerInterface $logger
    ) {
    }

    /**
     * Monitor all active alerts and trigger those whose conditions are met
     */
    public function monitor(): void
    {
        $this->logger->info('Starting alert monitoring cycle');

        $activeAlerts = $this->alertRepository->findAllActive();
        $triggeredCount = 0;

        foreach ($activeAlerts as $alert) {
            try {
                if (!$alert->shouldEvaluate()) {
                    continue;
                }

                if ($this->evaluateAlert($alert)) {
                    $triggeredCount++;
                }
            } catch (\Exception $e) {
                $this->logger->error('Failed to evaluate alert', [
                    'alertId' => $alert->getId()->getValue(),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->logger->info('Alert monitoring cycle completed', [
            'totalAlerts' => count($activeAlerts),
            'triggered' => $triggeredCount,
        ]);
    }

    /**
     * Monitor alerts for a specific symbol (called on price updates)
     */
    public function monitorSymbol(string $symbol, float $currentPrice): void
    {
        $alerts = $this->alertRepository->findActiveBySymbol($symbol);

        foreach ($alerts as $alert) {
            try {
                if (!$alert->shouldEvaluate()) {
                    continue;
                }

                $evaluator = $this->getEvaluator($alert->getType());
                if ($evaluator === null) {
                    continue;
                }

                if ($evaluator->evaluate($alert, $currentPrice)) {
                    $this->triggerService->triggerAlert($alert, $currentPrice);
                }
            } catch (\Exception $e) {
                $this->logger->error('Failed to evaluate symbol alert', [
                    'alertId' => $alert->getId()->getValue(),
                    'symbol' => $symbol,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function evaluateAlert(PriceAlert $alert): bool
    {
        $symbol = $alert->getSymbol();
        if ($symbol === null) {
            // Portfolio-level alerts would be handled differently
            return false;
        }

        // Get current market data
        $marketData = $this->marketDataProvider->getCurrentPrice($symbol);
        if ($marketData === null) {
            return false;
        }

        $evaluator = $this->getEvaluator($alert->getType());
        if ($evaluator === null) {
            return false;
        }

        $currentPrice = $marketData['price'];

        if ($evaluator->evaluate($alert, $currentPrice)) {
            $this->triggerService->triggerAlert($alert, $currentPrice);
            return true;
        }

        return false;
    }

    private function getEvaluator(\App\Alert\Domain\ValueObject\AlertType $type): ?AlertEvaluatorInterface
    {
        foreach ($this->evaluators as $evaluator) {
            if ($evaluator->supports($type)) {
                return $evaluator;
            }
        }

        return null;
    }
}
