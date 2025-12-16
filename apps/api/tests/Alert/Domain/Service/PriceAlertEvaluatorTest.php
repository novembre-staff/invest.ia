<?php

declare(strict_types=1);

namespace App\Tests\Alert\Domain\Service;

use App\Alert\Domain\Model\PriceAlert;
use App\Alert\Domain\Service\PriceAlertEvaluator;
use App\Alert\Domain\ValueObject\AlertCondition;
use App\Alert\Domain\ValueObject\AlertType;
use App\Alert\Domain\ValueObject\NotificationChannel;
use App\Identity\Domain\ValueObject\UserId;
use PHPUnit\Framework\TestCase;

class PriceAlertEvaluatorTest extends TestCase
{
    private PriceAlertEvaluator $evaluator;
    private UserId $userId;

    protected function setUp(): void
    {
        $this->evaluator = new PriceAlertEvaluator();
        $this->userId = UserId::generate();
    }

    public function test_supports_price_above_alerts(): void
    {
        $this->assertTrue($this->evaluator->supports(AlertType::PRICE_ABOVE));
    }

    public function test_supports_price_below_alerts(): void
    {
        $this->assertTrue($this->evaluator->supports(AlertType::PRICE_BELOW));
    }

    public function test_does_not_support_other_alert_types(): void
    {
        $this->assertFalse($this->evaluator->supports(AlertType::PRICE_CHANGE_PERCENT));
        $this->assertFalse($this->evaluator->supports(AlertType::VOLUME_SPIKE));
    }

    public function test_evaluates_price_above_when_price_exceeds_target(): void
    {
        $alert = PriceAlert::create(
            $this->userId,
            AlertType::PRICE_ABOVE,
            'BTC',
            AlertCondition::priceAbove(50000.0),
            [NotificationChannel::EMAIL]
        );

        $result = $this->evaluator->evaluate($alert, 51000.0);

        $this->assertTrue($result);
    }

    public function test_evaluates_price_above_when_price_equals_target(): void
    {
        $alert = PriceAlert::create(
            $this->userId,
            AlertType::PRICE_ABOVE,
            'BTC',
            AlertCondition::priceAbove(50000.0),
            [NotificationChannel::EMAIL]
        );

        $result = $this->evaluator->evaluate($alert, 50000.0);

        $this->assertTrue($result);
    }

    public function test_evaluates_price_above_when_price_below_target(): void
    {
        $alert = PriceAlert::create(
            $this->userId,
            AlertType::PRICE_ABOVE,
            'BTC',
            AlertCondition::priceAbove(50000.0),
            [NotificationChannel::EMAIL]
        );

        $result = $this->evaluator->evaluate($alert, 49000.0);

        $this->assertFalse($result);
    }

    public function test_evaluates_price_below_when_price_falls_below_target(): void
    {
        $alert = PriceAlert::create(
            $this->userId,
            AlertType::PRICE_BELOW,
            'BTC',
            AlertCondition::priceBelow(40000.0),
            [NotificationChannel::EMAIL]
        );

        $result = $this->evaluator->evaluate($alert, 39000.0);

        $this->assertTrue($result);
    }

    public function test_evaluates_price_below_when_price_equals_target(): void
    {
        $alert = PriceAlert::create(
            $this->userId,
            AlertType::PRICE_BELOW,
            'BTC',
            AlertCondition::priceBelow(40000.0),
            [NotificationChannel::EMAIL]
        );

        $result = $this->evaluator->evaluate($alert, 40000.0);

        $this->assertTrue($result);
    }

    public function test_evaluates_price_below_when_price_above_target(): void
    {
        $alert = PriceAlert::create(
            $this->userId,
            AlertType::PRICE_BELOW,
            'BTC',
            AlertCondition::priceBelow(40000.0),
            [NotificationChannel::EMAIL]
        );

        $result = $this->evaluator->evaluate($alert, 41000.0);

        $this->assertFalse($result);
    }
}
