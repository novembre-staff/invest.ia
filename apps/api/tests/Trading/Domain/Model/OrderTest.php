<?php

declare(strict_types=1);

namespace App\Tests\Trading\Domain\Model;

use App\Identity\Domain\ValueObject\UserId;
use App\Trading\Domain\Model\Order;
use App\Trading\Domain\ValueObject\OrderSide;
use App\Trading\Domain\ValueObject\OrderStatus;
use App\Trading\Domain\ValueObject\OrderType;
use App\Trading\Domain\ValueObject\TimeInForce;
use PHPUnit\Framework\TestCase;

class OrderTest extends TestCase
{
    private UserId $userId;

    protected function setUp(): void
    {
        $this->userId = UserId::generate();
    }

    public function test_creates_market_buy_order(): void
    {
        $order = Order::create(
            $this->userId,
            'BTCUSDT',
            OrderType::MARKET,
            OrderSide::BUY,
            0.001
        );

        $this->assertEquals('BTCUSDT', $order->getSymbol());
        $this->assertEquals(OrderType::MARKET, $order->getType());
        $this->assertEquals(OrderSide::BUY, $order->getSide());
        $this->assertEquals(0.001, $order->getQuantity());
        $this->assertEquals(OrderStatus::PENDING, $order->getStatus());
        $this->assertNull($order->getPrice());
    }

    public function test_creates_limit_sell_order(): void
    {
        $order = Order::create(
            $this->userId,
            'ETHUSDT',
            OrderType::LIMIT,
            OrderSide::SELL,
            1.0,
            3000.0
        );

        $this->assertEquals('ETHUSDT', $order->getSymbol());
        $this->assertEquals(OrderType::LIMIT, $order->getType());
        $this->assertEquals(OrderSide::SELL, $order->getSide());
        $this->assertEquals(1.0, $order->getQuantity());
        $this->assertEquals(3000.0, $order->getPrice());
    }

    public function test_requires_price_for_limit_orders(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('requires a price');

        Order::create(
            $this->userId,
            'BTCUSDT',
            OrderType::LIMIT,
            OrderSide::BUY,
            0.001,
            null // Missing required price
        );
    }

    public function test_requires_stop_price_for_stop_loss_orders(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('requires a stop price');

        Order::create(
            $this->userId,
            'BTCUSDT',
            OrderType::STOP_LOSS,
            OrderSide::SELL,
            0.001
        );
    }

    public function test_validates_positive_quantity(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Quantity must be greater than 0');

        Order::create(
            $this->userId,
            'BTCUSDT',
            OrderType::MARKET,
            OrderSide::BUY,
            0.0
        );
    }

    public function test_validates_positive_price(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Price must be greater than 0');

        Order::create(
            $this->userId,
            'BTCUSDT',
            OrderType::LIMIT,
            OrderSide::BUY,
            0.001,
            -100.0
        );
    }

    public function test_submits_order_successfully(): void
    {
        $order = Order::create(
            $this->userId,
            'BTCUSDT',
            OrderType::MARKET,
            OrderSide::BUY,
            0.001
        );

        $order->submit('12345678', 'client-order-123');

        $this->assertEquals(OrderStatus::NEW, $order->getStatus());
        $this->assertEquals('12345678', $order->getExchangeOrderId());
        $this->assertEquals('client-order-123', $order->getClientOrderId());
    }

    public function test_cannot_submit_non_pending_order(): void
    {
        $order = Order::create(
            $this->userId,
            'BTCUSDT',
            OrderType::MARKET,
            OrderSide::BUY,
            0.001
        );

        $order->submit('12345678', 'client-order-123');

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Only pending orders can be submitted');

        $order->submit('87654321', 'client-order-456');
    }

    public function test_updates_order_status_with_execution_data(): void
    {
        $order = Order::create(
            $this->userId,
            'BTCUSDT',
            OrderType::LIMIT,
            OrderSide::BUY,
            0.001,
            50000.0
        );

        $order->submit('12345678', 'client-order-123');
        $order->updateStatus(OrderStatus::PARTIALLY_FILLED, 0.0005, 25.0);

        $this->assertEquals(OrderStatus::PARTIALLY_FILLED, $order->getStatus());
        $this->assertEquals(0.0005, $order->getExecutedQuantity());
        $this->assertEquals(25.0, $order->getCumulativeQuoteQuantity());
        $this->assertEquals(50.0, $order->getFilledPercentage());
    }

    public function test_marks_as_filled_when_fully_executed(): void
    {
        $order = Order::create(
            $this->userId,
            'BTCUSDT',
            OrderType::MARKET,
            OrderSide::BUY,
            0.001
        );

        $order->submit('12345678', 'client-order-123');
        $order->updateStatus(OrderStatus::FILLED, 0.001, 50.0);

        $this->assertEquals(OrderStatus::FILLED, $order->getStatus());
        $this->assertNotNull($order->getExecutedAt());
        $this->assertEquals(100.0, $order->getFilledPercentage());
        $this->assertEquals(0.0, $order->getRemainingQuantity());
    }

    public function test_cancels_order_successfully(): void
    {
        $order = Order::create(
            $this->userId,
            'BTCUSDT',
            OrderType::LIMIT,
            OrderSide::BUY,
            0.001,
            50000.0
        );

        $order->submit('12345678', 'client-order-123');
        $order->cancel();

        $this->assertEquals(OrderStatus::CANCELLED, $order->getStatus());
    }

    public function test_cannot_cancel_filled_order(): void
    {
        $order = Order::create(
            $this->userId,
            'BTCUSDT',
            OrderType::MARKET,
            OrderSide::BUY,
            0.001
        );

        $order->submit('12345678', 'client-order-123');
        $order->updateStatus(OrderStatus::FILLED, 0.001, 50.0);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Order cannot be cancelled in current status');

        $order->cancel();
    }

    public function test_rejects_order_with_reason(): void
    {
        $order = Order::create(
            $this->userId,
            'BTCUSDT',
            OrderType::LIMIT,
            OrderSide::BUY,
            0.001,
            50000.0
        );

        $order->reject('Insufficient funds');

        $this->assertEquals(OrderStatus::REJECTED, $order->getStatus());
        $this->assertEquals('Insufficient funds', $order->getRejectReason());
    }

    public function test_calculates_average_price(): void
    {
        $order = Order::create(
            $this->userId,
            'BTCUSDT',
            OrderType::MARKET,
            OrderSide::BUY,
            0.001
        );

        $order->submit('12345678', 'client-order-123');
        $order->updateStatus(OrderStatus::FILLED, 0.001, 50.5);

        $this->assertEquals(50500.0, $order->getAveragePrice());
    }

    public function test_calculates_total_value_for_limit_order(): void
    {
        $order = Order::create(
            $this->userId,
            'BTCUSDT',
            OrderType::LIMIT,
            OrderSide::BUY,
            0.002,
            50000.0
        );

        $this->assertEquals(100.0, $order->getTotalValue());
    }

    public function test_normalizes_symbol_to_uppercase(): void
    {
        $order = Order::create(
            $this->userId,
            'btcusdt',
            OrderType::MARKET,
            OrderSide::BUY,
            0.001
        );

        $this->assertEquals('BTCUSDT', $order->getSymbol());
    }
}
