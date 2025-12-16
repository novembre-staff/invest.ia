<?php

declare(strict_types=1);

namespace App\Alert\Domain\ValueObject;

enum NotificationChannel: string
{
    case EMAIL = 'email';
    case PUSH = 'push';
    case IN_APP = 'in_app';
    case SMS = 'sms';

    public function getDisplayName(): string
    {
        return match($this) {
            self::EMAIL => 'Email',
            self::PUSH => 'Push Notification',
            self::IN_APP => 'In-App',
            self::SMS => 'SMS',
        };
    }
}
