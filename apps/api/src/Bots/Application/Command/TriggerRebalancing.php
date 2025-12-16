<?php

declare(strict_types=1);

namespace App\Bots\Application\Command;

final class TriggerRebalancing
{
    public function __construct(
        private readonly string $botId,
        private readonly string $reason
    ) {
    }

    public function botId(): string
    {
        return $this->botId;
    }

    public function reason(): string
    {
        return $this->reason;
    }
}
