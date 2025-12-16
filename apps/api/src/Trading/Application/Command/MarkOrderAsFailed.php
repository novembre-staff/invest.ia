<?php

declare(strict_types=1);

namespace App\Trading\Application\Command;

use App\Trading\Domain\ValueObject\OrderId;

final readonly class MarkOrderAsFailed
{
    public function __construct(
        public OrderId $orderId,
        public string $errorCode,
        public string $errorMessage
    ) {
    }
}
