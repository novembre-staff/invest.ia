<?php

declare(strict_types=1);

namespace App\Tests\Alert\Domain\Service;

use App\Alert\Domain\Model\PriceAlert;
use App\Alert\Domain\Service\PercentChangeAlertEvaluator;
use App\Alert\Domain\ValueObject\AlertCondition;
use App\Alert\Domain\ValueObject\AlertType;
use App\Alert\Domain\ValueObject\NotificationChannel;
use App\Identity\Domain\ValueObject\UserId;
use PHPUnit\Framework\TestCase;

class PercentChangeAlertEvaluatorTest extends TestCase
{
    private PercentChangeAlertEvaluator $evaluator;
    private UserId $userId;

    protected function setUp(): void
    {
        $this->evaluator = new PercentChangeAlertEvaluator();
        $this->userId = UserId::generate();
    }

    public function test_supports_price_change_percent_alerts(): void
    {
        $this->assertTrue($this->evaluator->supports(AlertType::PRICE_CHANGE_PERCENT));
    }

    public function test_does_not_support_other_alert_types(): void
    {
        $this->assertFalse($this->evaluator->supports(AlertType::PRICE_ABOVE));
        $this->assertFalse($this->evaluator->supports(AlertType::PRICE_BELOW));
    }

    public function test_evaluates_positive_percent_change(): void
    {
        $alert = PriceAlert::create(
            $this->userId,
            AlertType::PRICE_CHANGE_PERCENT,
            'BTC',
            AlertCondition::percentChange(10.0), // 10% change
            [NotificationChannel::EMAIL]
        );

        // Price increased from 50000 to 55500 (11% increase)
        $result = $this->evaluator->evaluate($alert, 55500.0, 50000.0);

        $this->assertTrue($result);
    }

    public function test_evaluates_negative_percent_change(): void
    {
        $alert = PriceAlert::create(
            $this->userId,
            AlertType::PRICE_CHANGE_PERCENT,
            'BTC',
            AlertCondition::percentChange(10.0), // 10% change (absolute)
            [NotificationChannel::EMAIL]
        );

        // Price decreased from 50000 to 44500 (11% decrease)
        $result = $this->evaluator->evaluate($alert, 44500.0, 50000.0);

        $this->assertTrue($result);
    }

    public function test_does_not_trigger_when_change_below_threshold(): void
    {
        $alert = PriceAlert::create(
            $this->userId,
            AlertType::PRICE_CHANGE_PERCENT,
            'BTC',
            AlertCondition::percentChange(10.0),
            [NotificationChannel::EMAIL]
        );

        // Price increased from 50000 to 52500 (5% increase)
        $result = $this->evaluator->evaluate($alert, 52500.0, 50000.0);

        $this->assertFalse($result);
    }

    public function test_handles_missing_previous_value(): void
    {
        $alert = PriceAlert::create(
            $this->userId,
            AlertType::PRICE_CHANGE_PERCENT,
            'BTC',
            AlertCondition::percentChange(10.0),
            [NotificationChannel::EMAIL]
        );

        // No previous value to compare
        $result = $this->evaluator->evaluate($alert, 55000.0, null);

        $this->assertFalse($result);
    }

    public function test_handles_zero_previous_value(): void
    {
        $alert = PriceAlert::create(
            $this->userId,
            AlertType::PRICE_CHANGE_PERCENT,
            'BTC',
            AlertCondition::percentChange(10.0),
            [NotificationChannel::EMAIL]
        );

        // Previous value is zero (division by zero)
        $result = $this->evaluator->evaluate($alert, 55000.0, 0.0);

        $this->assertFalse($result);
    }

    public function test_calculates_percent_change_correctly(): void
    {
        $alert = PriceAlert::create(
            $this->userId,
            AlertType::PRICE_CHANGE_PERCENT,
            'BTC',
            AlertCondition::percentChange(5.0), // 5% threshold
            [NotificationChannel::EMAIL]
        );

        // Exactly 5% increase (from 40000 to 42000)
        $result = $this->evaluator->evaluate($alert, 42000.0, 40000.0);

        $this->assertTrue($result);
    }
}
