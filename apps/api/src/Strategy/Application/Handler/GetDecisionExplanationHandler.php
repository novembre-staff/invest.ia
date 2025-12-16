<?php

declare(strict_types=1);

namespace App\Strategy\Application\Handler;

use App\Strategy\Application\Query\GetDecisionExplanation;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetDecisionExplanationHandler
{
    public function __invoke(GetDecisionExplanation $query): array
    {
        // TODO: Fetch from decision log/audit
        // For now, return structured explanation format
        
        return [
            'decision_id' => $query->decisionId,
            'bot_id' => $query->botId->toString(),
            'type' => 'entry',  // or 'exit', 'hold', etc.
            'timestamp' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
            
            'reasoning' => [
                'primary_factors' => [
                    'Technical indicators aligned (RSI: 32, oversold)',
                    'Volume spike detected (+150%)',
                    'Support level confirmed at $42,500'
                ],
                'market_conditions' => [
                    'Overall market sentiment: positive',
                    'BTC dominance: 52%',
                    'Fear & Greed Index: 45 (neutral)'
                ],
                'risk_assessment' => [
                    'Risk score: 3/10 (low)',
                    'Max drawdown potential: 8%',
                    'Position size: 2% of portfolio'
                ]
            ],
            
            'data_points' => [
                'entry_price' => 43250.00,
                'target_price' => 46500.00,
                'stop_loss' => 41800.00,
                'expected_return' => '+7.5%',
                'risk_reward_ratio' => 2.24
            ],
            
            'confidence_level' => 0.78,
            
            'alternative_considered' => 'Wait for retest of $41,000 support',
            'why_not_chosen' => 'Strong momentum and volume suggest immediate entry is preferable'
        ];
    }
}
