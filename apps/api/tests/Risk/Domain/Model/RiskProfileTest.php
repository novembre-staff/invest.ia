<?php

declare(strict_types=1);

namespace Tests\Risk\Domain\Model;

use App\Identity\Domain\ValueObject\UserId;
use App\Risk\Domain\Model\RiskProfile;
use App\Risk\Domain\ValueObject\RiskLevel;
use PHPUnit\Framework\TestCase;

class RiskProfileTest extends TestCase
{
    public function test_creates_risk_profile_with_default_limits(): void
    {
        $profile = RiskProfile::create(
            userId: UserId::generate(),
            riskLevel: RiskLevel::MODERATE
        );

        $this->assertEquals(RiskLevel::MODERATE, $profile->getRiskLevel());
        $this->assertEquals(20.0, $profile->getMaxPositionSizePercent());
        $this->assertEquals(100.0, $profile->getMaxPortfolioExposurePercent());
        $this->assertEquals(5.0, $profile->getMaxDailyLossPercent());
        $this->assertEquals(20.0, $profile->getMaxDrawdownPercent());
        $this->assertEquals(1.0, $profile->getMaxLeverage());
    }

    public function test_creates_risk_profile_with_custom_limits(): void
    {
        $profile = RiskProfile::create(
            userId: UserId::generate(),
            riskLevel: RiskLevel::HIGH,
            maxPositionSizePercent: 25.0,
            maxPortfolioExposurePercent: 150.0,
            maxDailyLossPercent: 8.0,
            maxDrawdownPercent: 25.0,
            maxLeverage: 2.0,
            maxConcentrationPercent: 40.0,
            maxTradesPerDay: 20
        );

        $this->assertEquals(25.0, $profile->getMaxPositionSizePercent());
        $this->assertEquals(150.0, $profile->getMaxPortfolioExposurePercent());
        $this->assertEquals(8.0, $profile->getMaxDailyLossPercent());
        $this->assertEquals(2.0, $profile->getMaxLeverage());
        $this->assertEquals(40.0, $profile->getMaxConcentrationPercent());
        $this->assertEquals(20, $profile->getMaxTradesPerDay());
    }

    public function test_validates_position_size_limits(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Max position size must be between 0 and 100%');

        RiskProfile::create(
            userId: UserId::generate(),
            riskLevel: RiskLevel::MODERATE,
            maxPositionSizePercent: 150.0
        );
    }

    public function test_validates_leverage_limits(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Max leverage must be between 1 and 100');

        RiskProfile::create(
            userId: UserId::generate(),
            riskLevel: RiskLevel::MODERATE,
            maxLeverage: 0.5
        );
    }

    public function test_updates_limits_successfully(): void
    {
        $profile = RiskProfile::create(
            userId: UserId::generate(),
            riskLevel: RiskLevel::MODERATE
        );

        $profile->updateLimits(
            maxPositionSizePercent: 15.0,
            maxPortfolioExposurePercent: 120.0,
            maxDailyLossPercent: 4.0,
            maxDrawdownPercent: 15.0,
            maxLeverage: 1.5,
            maxConcentrationPercent: 30.0,
            maxTradesPerDay: 15
        );

        $this->assertEquals(15.0, $profile->getMaxPositionSizePercent());
        $this->assertEquals(120.0, $profile->getMaxPortfolioExposurePercent());
        $this->assertEquals(4.0, $profile->getMaxDailyLossPercent());
    }

    public function test_changes_risk_level_and_adjusts_limits(): void
    {
        $profile = RiskProfile::create(
            userId: UserId::generate(),
            riskLevel: RiskLevel::HIGH,
            maxPositionSizePercent: 30.0,
            maxDailyLossPercent: 10.0,
            maxDrawdownPercent: 30.0
        );

        // Change to lower risk level
        $profile->changeRiskLevel(RiskLevel::LOW);

        $this->assertEquals(RiskLevel::LOW, $profile->getRiskLevel());
        // Limits should be adjusted down to match new level
        $this->assertEquals(10.0, $profile->getMaxPositionSizePercent()); // LOW max is 10%
        $this->assertEquals(3.0, $profile->getMaxDailyLossPercent()); // LOW max is 3%
        $this->assertEquals(10.0, $profile->getMaxDrawdownPercent()); // LOW max is 10%
    }

    public function test_checks_if_position_size_is_allowed(): void
    {
        $profile = RiskProfile::create(
            userId: UserId::generate(),
            riskLevel: RiskLevel::MODERATE,
            maxPositionSizePercent: 20.0
        );

        $this->assertTrue($profile->isPositionSizeAllowed(15.0));
        $this->assertTrue($profile->isPositionSizeAllowed(20.0));
        $this->assertFalse($profile->isPositionSizeAllowed(25.0));
    }

    public function test_checks_if_daily_loss_is_allowed(): void
    {
        $profile = RiskProfile::create(
            userId: UserId::generate(),
            riskLevel: RiskLevel::MODERATE,
            maxDailyLossPercent: 5.0
        );

        $this->assertTrue($profile->isDailyLossAllowed(3.0));
        $this->assertTrue($profile->isDailyLossAllowed(-4.0)); // Negative loss
        $this->assertFalse($profile->isDailyLossAllowed(-6.0)); // abs(-6) > 5
    }

    public function test_checks_if_drawdown_is_allowed(): void
    {
        $profile = RiskProfile::create(
            userId: UserId::generate(),
            riskLevel: RiskLevel::LOW,
            maxDrawdownPercent: 10.0
        );

        $this->assertTrue($profile->isDrawdownAllowed(8.0));
        $this->assertTrue($profile->isDrawdownAllowed(10.0));
        $this->assertFalse($profile->isDrawdownAllowed(12.0));
    }

    public function test_checks_if_leverage_is_allowed(): void
    {
        $profile = RiskProfile::create(
            userId: UserId::generate(),
            riskLevel: RiskLevel::HIGH,
            maxLeverage: 3.0
        );

        $this->assertTrue($profile->isLeverageAllowed(2.5));
        $this->assertTrue($profile->isLeverageAllowed(3.0));
        $this->assertFalse($profile->isLeverageAllowed(3.5));
    }

    public function test_different_risk_levels_have_different_defaults(): void
    {
        $veryLow = RiskProfile::create(UserId::generate(), RiskLevel::VERY_LOW);
        $moderate = RiskProfile::create(UserId::generate(), RiskLevel::MODERATE);
        $veryHigh = RiskProfile::create(UserId::generate(), RiskLevel::VERY_HIGH);

        $this->assertEquals(5.0, $veryLow->getMaxPositionSizePercent());
        $this->assertEquals(20.0, $moderate->getMaxPositionSizePercent());
        $this->assertEquals(50.0, $veryHigh->getMaxPositionSizePercent());

        $this->assertEquals(2.0, $veryLow->getMaxDailyLossPercent());
        $this->assertEquals(5.0, $moderate->getMaxDailyLossPercent());
        $this->assertEquals(15.0, $veryHigh->getMaxDailyLossPercent());
    }
}
