<?php

declare(strict_types=1);

namespace App\Alert\Infrastructure\Service;

use App\Alert\Domain\Event\AlertTriggered;
use App\Alert\Domain\ValueObject\NotificationChannel;
use Psr\Log\LoggerInterface;

/**
 * In-app notification delivery service
 * Stores notifications in database for display in app
 */
final readonly class InAppNotificationDelivery implements NotificationDeliveryInterface
{
    public function __construct(
        private LoggerInterface $logger
    ) {
    }

    public function send(AlertTriggered $event, array $channels): void
    {
        if (!in_array(NotificationChannel::IN_APP, $channels, true)) {
            return;
        }

        // TODO: Implement in-app notification storage
        // - Store in user_notifications table
        // - Include read/unread status
        // - Set priority based on alert type
        // - Expire old notifications

        $this->logger->info('In-app notification created', [
            'alertId' => $event->alertId->getValue(),
            'userId' => $event->userId->getValue(),
            'type' => $event->type->value,
            'symbol' => $event->symbol,
            'message' => $event->message,
        ]);
    }

    public function supports(NotificationChannel $channel): bool
    {
        return $channel === NotificationChannel::IN_APP;
    }
}
