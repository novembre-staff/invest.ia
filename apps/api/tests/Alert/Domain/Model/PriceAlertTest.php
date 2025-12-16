<?php

declare(strict_types=1);

namespace App\Tests\Alert\Domain\Model;

use App\Alert\Domain\Model\PriceAlert;
use App\Alert\Domain\ValueObject\AlertCondition;
use App\Alert\Domain\ValueObject\AlertStatus;
use App\Alert\Domain\ValueObject\AlertType;
use App\Alert\Domain\ValueObject\NotificationChannel;
use App\Identity\Domain\ValueObject\UserId;
use PHPUnit\Framework\TestCase;

class PriceAlertTest extends TestCase
{
    private UserId $userId;

    protected function setUp(): void
    {
        $this->userId = UserId::generate();
    }

    public function test_creates_price_alert_successfully(): void
    {
        $alert = PriceAlert::create(
            $this->userId,
            AlertType::PRICE_ABOVE,
            'BTC',
            AlertCondition::priceAbove(50000.0),
            [NotificationChannel::EMAIL, NotificationChannel::PUSH],
            'Bitcoin reached target'
        );

        $this->assertNotNull($alert->getId());
        $this->assertEquals($this->userId, $alert->getUserId());
        $this->assertEquals(AlertType::PRICE_ABOVE, $alert->getType());
        $this->assertEquals('BTC', $alert->getSymbol());
        $this->assertEquals(50000.0, $alert->getCondition()->getTargetValue());
        $this->assertEquals(AlertStatus::ACTIVE, $alert->getStatus());
        $this->assertCount(2, $alert->getNotificationChannels());
    }

    public function test_requires_symbol_for_price_alerts(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('requires a symbol');

        PriceAlert::create(
            $this->userId,
            AlertType::PRICE_ABOVE,
            null, // Missing required symbol
            AlertCondition::priceAbove(50000.0),
            [NotificationChannel::EMAIL]
        );
    }

    public function test_requires_at_least_one_notification_channel(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('At least one notification channel is required');

        PriceAlert::create(
            $this->userId,
            AlertType::PRICE_ABOVE,
            'BTC',
            AlertCondition::priceAbove(50000.0),
            [] // Empty channels
        );
    }

    public function test_triggers_alert_successfully(): void
    {
        $alert = PriceAlert::create(
            $this->userId,
            AlertType::PRICE_ABOVE,
            'BTC',
            AlertCondition::priceAbove(50000.0),
            [NotificationChannel::EMAIL]
        );

        $triggeredAt = new \DateTimeImmutable();
        $alert->trigger($triggeredAt);

        $this->assertEquals(AlertStatus::TRIGGERED, $alert->getStatus());
        $this->assertEquals($triggeredAt, $alert->getTriggeredAt());
    }

    public function test_cannot_trigger_non_active_alert(): void
    {
        $alert = PriceAlert::create(
            $this->userId,
            AlertType::PRICE_ABOVE,
            'BTC',
            AlertCondition::priceAbove(50000.0),
            [NotificationChannel::EMAIL]
        );

        $alert->trigger();

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Only active alerts can be triggered');

        $alert->trigger(); // Try to trigger again
    }

    public function test_cancels_alert_successfully(): void
    {
        $alert = PriceAlert::create(
            $this->userId,
            AlertType::PRICE_ABOVE,
            'BTC',
            AlertCondition::priceAbove(50000.0),
            [NotificationChannel::EMAIL]
        );

        $alert->cancel();

        $this->assertEquals(AlertStatus::CANCELLED, $alert->getStatus());
    }

    public function test_cannot_cancel_non_active_alert(): void
    {
        $alert = PriceAlert::create(
            $this->userId,
            AlertType::PRICE_ABOVE,
            'BTC',
            AlertCondition::priceAbove(50000.0),
            [NotificationChannel::EMAIL]
        );

        $alert->trigger();

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Only active alerts can be cancelled');

        $alert->cancel();
    }

    public function test_detects_expired_alerts(): void
    {
        $expiresAt = new \DateTimeImmutable('-1 hour');

        $alert = PriceAlert::create(
            $this->userId,
            AlertType::PRICE_ABOVE,
            'BTC',
            AlertCondition::priceAbove(50000.0),
            [NotificationChannel::EMAIL],
            null,
            $expiresAt
        );

        $this->assertTrue($alert->isExpired());
    }

    public function test_should_not_evaluate_expired_alerts(): void
    {
        $expiresAt = new \DateTimeImmutable('-1 hour');

        $alert = PriceAlert::create(
            $this->userId,
            AlertType::PRICE_ABOVE,
            'BTC',
            AlertCondition::priceAbove(50000.0),
            [NotificationChannel::EMAIL],
            null,
            $expiresAt
        );

        $shouldEvaluate = $alert->shouldEvaluate();

        $this->assertFalse($shouldEvaluate);
        $this->assertEquals(AlertStatus::EXPIRED, $alert->getStatus());
    }

    public function test_matches_by_type_and_symbol(): void
    {
        $alert = PriceAlert::create(
            $this->userId,
            AlertType::PRICE_ABOVE,
            'BTC',
            AlertCondition::priceAbove(50000.0),
            [NotificationChannel::EMAIL]
        );

        $this->assertTrue($alert->matches(AlertType::PRICE_ABOVE, 'BTC'));
        $this->assertTrue($alert->matches(AlertType::PRICE_ABOVE, null));
        $this->assertFalse($alert->matches(AlertType::PRICE_BELOW, 'BTC'));
        $this->assertFalse($alert->matches(AlertType::PRICE_ABOVE, 'ETH'));
    }

    public function test_portfolio_alert_does_not_require_symbol(): void
    {
        $alert = PriceAlert::create(
            $this->userId,
            AlertType::PORTFOLIO_VALUE,
            null, // No symbol required for portfolio alerts
            AlertCondition::portfolioValue(100000.0),
            [NotificationChannel::EMAIL]
        );

        $this->assertNull($alert->getSymbol());
        $this->assertEquals(AlertType::PORTFOLIO_VALUE, $alert->getType());
    }
}
