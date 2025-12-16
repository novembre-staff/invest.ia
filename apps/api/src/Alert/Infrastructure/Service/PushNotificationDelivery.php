<?php

declare(strict_types=1);

namespace App\Alert\Infrastructure\Service;

use App\Alert\Domain\Event\AlertTriggered;
use App\Alert\Domain\ValueObject\NotificationChannel;
use Psr\Log\LoggerInterface;

/**
 * Push notification delivery service
 * In production, this would use Firebase Cloud Messaging or similar
 */
final readonly class PushNotificationDelivery implements NotificationDeliveryInterface
{
    public function __construct(
        private LoggerInterface $logger
    ) {
    }

    public function send(AlertTriggered $event, array $channels): void
    {
        if (!in_array(NotificationChannel::PUSH, $channels, true)) {
            return;
        }

        // TODO: Implement actual push notification sending
        // - Load user FCM tokens from database
        // - Build notification payload
        // - Send via Firebase Cloud Messaging
        // - Handle token expiration/errors

        $this->logger->info('Push notification sent', [
            'alertId' => $event->alertId->getValue(),
            'userId' => $event->userId->getValue(),
            'type' => $event->type->value,
            'symbol' => $event->symbol,
            'message' => $event->message,
        ]);
    }

    public function supports(NotificationChannel $channel): bool
    {
        return $channel === NotificationChannel::PUSH;
    }
}
