<?php

declare(strict_types=1);

namespace App\Alert\Infrastructure\Service;

use App\Alert\Domain\Event\AlertTriggered;
use App\Alert\Domain\ValueObject\NotificationChannel;
use Psr\Log\LoggerInterface;

/**
 * Email notification delivery service
 * In production, this would use Symfony Mailer or similar
 */
final readonly class EmailNotificationDelivery implements NotificationDeliveryInterface
{
    public function __construct(
        private LoggerInterface $logger
    ) {
    }

    public function send(AlertTriggered $event, array $channels): void
    {
        if (!in_array(NotificationChannel::EMAIL, $channels, true)) {
            return;
        }

        // TODO: Implement actual email sending
        // - Load user email from database
        // - Render email template
        // - Send via Symfony Mailer
        // - Track delivery status

        $this->logger->info('Email notification sent', [
            'alertId' => $event->alertId->getValue(),
            'userId' => $event->userId->getValue(),
            'type' => $event->type->value,
            'symbol' => $event->symbol,
            'message' => $event->message,
        ]);
    }

    public function supports(NotificationChannel $channel): bool
    {
        return $channel === NotificationChannel::EMAIL;
    }
}
