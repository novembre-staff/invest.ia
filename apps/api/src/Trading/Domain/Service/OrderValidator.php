<?php

declare(strict_types=1);

namespace App\Trading\Domain\Service;

use App\Trading\Domain\Model\Order;
use App\Trading\Domain\ValueObject\OrderSide;
use App\Trading\Domain\ValueObject\OrderType;

/**
 * Basic order validation rules
 */
final class OrderValidator implements OrderValidatorInterface
{
    private const MIN_ORDER_VALUE_USDT = 10.0;
    private const MAX_ORDER_VALUE_USDT = 1000000.0;

    public function validate(Order $order): void
    {
        $this->validateSymbol($order->getSymbol());
        $this->validateQuantity($order->getQuantity());
        $this->validatePriceRange($order);
        $this->validateOrderValue($order);
    }

    private function validateSymbol(string $symbol): void
    {
        if (strlen($symbol) < 3) {
            throw new \DomainException('Invalid trading symbol');
        }

        // Most crypto pairs end with USDT, BTC, ETH, etc.
        $validQuotes = ['USDT', 'BTC', 'ETH', 'BNB', 'BUSD'];
        $hasValidQuote = false;
        
        foreach ($validQuotes as $quote) {
            if (str_ends_with($symbol, $quote)) {
                $hasValidQuote = true;
                break;
            }
        }

        if (!$hasValidQuote) {
            throw new \DomainException(
                'Symbol must end with a valid quote currency (USDT, BTC, ETH, BNB, BUSD)'
            );
        }
    }

    private function validateQuantity(float $quantity): void
    {
        if ($quantity <= 0) {
            throw new \DomainException('Order quantity must be greater than 0');
        }

        // Check for reasonable precision (max 8 decimals)
        $decimals = strlen(substr(strrchr((string)$quantity, '.'), 1));
        if ($decimals > 8) {
            throw new \DomainException('Order quantity has too many decimal places (max 8)');
        }
    }

    private function validatePriceRange(Order $order): void
    {
        $price = $order->getPrice();
        $stopPrice = $order->getStopPrice();

        // For stop orders, validate price relationships
        if ($stopPrice !== null && $price !== null) {
            if ($order->getSide() === OrderSide::BUY) {
                // For buy stop orders, stop price should be above limit price
                if ($order->getType() === OrderType::STOP_LOSS_LIMIT && $stopPrice < $price) {
                    throw new \DomainException(
                        'For buy stop-loss orders, stop price must be >= limit price'
                    );
                }
            } else {
                // For sell stop orders, stop price should be below limit price
                if ($order->getType() === OrderType::STOP_LOSS_LIMIT && $stopPrice > $price) {
                    throw new \DomainException(
                        'For sell stop-loss orders, stop price must be <= limit price'
                    );
                }
            }
        }
    }

    private function validateOrderValue(Order $order): void
    {
        // Only validate value for USDT pairs
        if (!str_ends_with($order->getSymbol(), 'USDT')) {
            return;
        }

        $value = $order->getTotalValue();
        
        // For market orders without executed value, we can't validate yet
        if ($order->getType() === OrderType::MARKET && $value === 0.0) {
            return;
        }

        if ($value < self::MIN_ORDER_VALUE_USDT) {
            throw new \DomainException(
                sprintf('Order value must be at least $%.2f USDT', self::MIN_ORDER_VALUE_USDT)
            );
        }

        if ($value > self::MAX_ORDER_VALUE_USDT) {
            throw new \DomainException(
                sprintf('Order value cannot exceed $%.2f USDT', self::MAX_ORDER_VALUE_USDT)
            );
        }
    }
}
