<?php

declare(strict_types=1);

namespace App\Tests\Trading\Domain\Service;

use App\Identity\Domain\ValueObject\UserId;
use App\Trading\Domain\Model\Order;
use App\Trading\Domain\Service\OrderValidator;
use App\Trading\Domain\ValueObject\OrderSide;
use App\Trading\Domain\ValueObject\OrderType;
use PHPUnit\Framework\TestCase;

class OrderValidatorTest extends TestCase
{
    private OrderValidator $validator;
    private UserId $userId;

    protected function setUp(): void
    {
        $this->validator = new OrderValidator();
        $this->userId = UserId::generate();
    }

    public function test_validates_valid_btcusdt_order(): void
    {
        $order = Order::create(
            $this->userId,
            'BTCUSDT',
            OrderType::LIMIT,
            OrderSide::BUY,
            0.001,
            50000.0
        );

        $this->validator->validate($order);
        $this->assertTrue(true); // No exception thrown
    }

    public function test_rejects_invalid_symbol(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Invalid trading symbol');

        $order = Order::create(
            $this->userId,
            'XY',
            OrderType::MARKET,
            OrderSide::BUY,
            1.0
        );

        $this->validator->validate($order);
    }

    public function test_rejects_symbol_without_valid_quote_currency(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Symbol must end with a valid quote currency');

        $order = Order::create(
            $this->userId,
            'BTCXYZ',
            OrderType::MARKET,
            OrderSide::BUY,
            0.001
        );

        $this->validator->validate($order);
    }

    public function test_validates_usdt_pairs(): void
    {
        $order = Order::create(
            $this->userId,
            'ETHUSDT',
            OrderType::MARKET,
            OrderSide::BUY,
            1.0
        );

        $this->validator->validate($order);
        $this->assertTrue(true);
    }

    public function test_validates_btc_pairs(): void
    {
        $order = Order::create(
            $this->userId,
            'ETHBTC',
            OrderType::MARKET,
            OrderSide::BUY,
            1.0
        );

        $this->validator->validate($order);
        $this->assertTrue(true);
    }

    public function test_rejects_order_below_minimum_value_for_usdt_pairs(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Order value must be at least $10.00 USDT');

        $order = Order::create(
            $this->userId,
            'BTCUSDT',
            OrderType::LIMIT,
            OrderSide::BUY,
            0.0001, // $5 at $50k
            50000.0
        );

        $this->validator->validate($order);
    }

    public function test_rejects_order_above_maximum_value_for_usdt_pairs(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Order value cannot exceed $1000000.00 USDT');

        $order = Order::create(
            $this->userId,
            'BTCUSDT',
            OrderType::LIMIT,
            OrderSide::BUY,
            25.0, // $1.25M at $50k
            50000.0
        );

        $this->validator->validate($order);
    }

    public function test_rejects_quantity_with_too_many_decimals(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Order quantity has too many decimal places');

        $order = Order::create(
            $this->userId,
            'BTCUSDT',
            OrderType::LIMIT,
            OrderSide::BUY,
            0.000000001, // 9 decimals
            50000.0
        );

        $this->validator->validate($order);
    }

    public function test_validates_stop_loss_limit_buy_order_price_relationships(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('stop price must be >= limit price');

        // For buy stop-loss, stop price should be >= limit price
        $order = Order::create(
            $this->userId,
            'BTCUSDT',
            OrderType::STOP_LOSS_LIMIT,
            OrderSide::BUY,
            0.001,
            50000.0, // limit price
            48000.0  // stop price (invalid: should be >= limit price)
        );

        $this->validator->validate($order);
    }

    public function test_validates_stop_loss_limit_sell_order_price_relationships(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('stop price must be <= limit price');

        // For sell stop-loss, stop price should be <= limit price
        $order = Order::create(
            $this->userId,
            'BTCUSDT',
            OrderType::STOP_LOSS_LIMIT,
            OrderSide::SELL,
            0.001,
            50000.0, // limit price
            52000.0  // stop price (invalid: should be <= limit price)
        );

        $this->validator->validate($order);
    }

    public function test_allows_valid_buy_stop_loss_limit_order(): void
    {
        $order = Order::create(
            $this->userId,
            'BTCUSDT',
            OrderType::STOP_LOSS_LIMIT,
            OrderSide::BUY,
            0.001,
            50000.0, // limit price
            51000.0  // stop price (valid: >= limit price)
        );

        $this->validator->validate($order);
        $this->assertTrue(true);
    }

    public function test_allows_valid_sell_stop_loss_limit_order(): void
    {
        $order = Order::create(
            $this->userId,
            'BTCUSDT',
            OrderType::STOP_LOSS_LIMIT,
            OrderSide::SELL,
            0.001,
            50000.0, // limit price
            49000.0  // stop price (valid: <= limit price)
        );

        $this->validator->validate($order);
        $this->assertTrue(true);
    }
}
