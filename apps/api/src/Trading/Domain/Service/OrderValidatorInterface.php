<?php

declare(strict_types=1);

namespace App\Trading\Domain\Service;

use App\Trading\Domain\Model\Order;

/**
 * Validates orders before submission
 */
interface OrderValidatorInterface
{
    /**
     * Validate an order before submission
     * @throws \DomainException if validation fails
     */
    public function validate(Order $order): void;
}
