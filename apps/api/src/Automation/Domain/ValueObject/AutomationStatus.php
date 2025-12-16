<?php

declare(strict_types=1);

namespace App\Automation\Domain\ValueObject;

enum AutomationStatus: string
{
    case DRAFT = 'draft';
    case ACTIVE = 'active';
    case PAUSED = 'paused';
    case STOPPED = 'stopped';
    case COMPLETED = 'completed';
    case FAILED = 'failed';

    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    public function canBeActivated(): bool
    {
        return in_array($this, [self::DRAFT, self::PAUSED, self::STOPPED]);
    }

    public function canBePaused(): bool
    {
        return $this === self::ACTIVE;
    }

    public function canBeStopped(): bool
    {
        return in_array($this, [self::ACTIVE, self::PAUSED]);
    }
}
