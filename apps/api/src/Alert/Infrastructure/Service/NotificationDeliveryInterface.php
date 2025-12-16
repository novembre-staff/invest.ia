<?php

declare(strict_types=1);

namespace App\Alert\Infrastructure\Service;

use App\Alert\Domain\Event\AlertTriggered;
use App\Alert\Domain\ValueObject\NotificationChannel;

interface NotificationDeliveryInterface
{
    /**
     * Send notification through this channel
     */
    public function send(AlertTriggered $event, array $channels): void;

    /**
     * Check if this delivery service supports the given channel
     */
    public function supports(NotificationChannel $channel): bool;
}
