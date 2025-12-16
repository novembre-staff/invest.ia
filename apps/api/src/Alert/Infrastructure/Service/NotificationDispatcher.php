<?php

declare(strict_types=1);

namespace App\Alert\Infrastructure\Service;

use App\Alert\Domain\Event\AlertTriggered;
use App\Alert\Domain\Repository\PriceAlertRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Orchestrates notification delivery across multiple channels
 */
final readonly class NotificationDispatcher
{
    /**
     * @param NotificationDeliveryInterface[] $deliveryServices
     */
    public function __construct(
        private iterable $deliveryServices,
        private PriceAlertRepositoryInterface $alertRepository,
        private LoggerInterface $logger
    ) {
    }

    public function dispatch(AlertTriggered $event): void
    {
        try {
            // Load alert to get notification channels
            $alert = $this->alertRepository->findById($event->alertId);
            
            if ($alert === null) {
                $this->logger->warning('Cannot dispatch notification for deleted alert', [
                    'alertId' => $event->alertId->getValue(),
                ]);
                return;
            }

            $channels = $alert->getNotificationChannels();

            // Send through each configured delivery service
            foreach ($this->deliveryServices as $service) {
                try {
                    $service->send($event, $channels);
                } catch (\Exception $e) {
                    $this->logger->error('Notification delivery failed', [
                        'service' => get_class($service),
                        'alertId' => $event->alertId->getValue(),
                        'error' => $e->getMessage(),
                    ]);
                    // Continue with other delivery services even if one fails
                }
            }
        } catch (\Exception $e) {
            $this->logger->error('Failed to dispatch notifications', [
                'alertId' => $event->alertId->getValue(),
                'error' => $e->getMessage(),
            ]);
        }
    }
}
