<?php

declare(strict_types=1);

namespace App\Risk\Domain\Service;

use App\Identity\Domain\ValueObject\UserId;
use App\Risk\Domain\Model\RiskAssessment;

interface RiskCalculatorInterface
{
    /**
     * Calculate comprehensive risk assessment for a user
     */
    public function calculateRiskAssessment(UserId $userId): RiskAssessment;

    /**
     * Calculate Value at Risk (VaR) for a portfolio
     * 
     * @param array $returns Array of historical returns
     * @param float $confidenceLevel Confidence level (e.g., 0.95 for 95%)
     * @return float VaR value
     */
    public function calculateVaR(array $returns, float $confidenceLevel = 0.95): float;

    /**
     * Calculate Sharpe Ratio
     * 
     * @param array $returns Array of returns
     * @param float $riskFreeRate Risk-free rate (annual)
     * @return float Sharpe ratio
     */
    public function calculateSharpeRatio(array $returns, float $riskFreeRate = 0.02): float;

    /**
     * Calculate portfolio volatility (standard deviation)
     */
    public function calculateVolatility(array $returns): float;

    /**
     * Calculate maximum drawdown
     */
    public function calculateMaxDrawdown(array $equityCurve): float;
}
