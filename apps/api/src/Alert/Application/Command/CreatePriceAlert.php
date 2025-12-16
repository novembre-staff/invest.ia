<?php

declare(strict_types=1);

namespace App\Alert\Application\Command;

use App\Alert\Domain\ValueObject\AlertType;

final readonly class CreatePriceAlert
{
    /**
     * @param string[] $notificationChannels
     */
    public function __construct(
        public string $userId,
        public string $type,
        public ?string $symbol,
        public float $targetValue,
        public array $notificationChannels,
        public ?string $message = null,
        public ?string $expiresAt = null
    ) {
    }
}
