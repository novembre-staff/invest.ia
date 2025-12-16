<?php

declare(strict_types=1);

namespace App\Strategy\Application\Handler;

use App\Strategy\Application\Query\GetPositionClosureExplanation;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetPositionClosureExplanationHandler
{
    public function __invoke(GetPositionClosureExplanation $query): array
    {
        // TODO: Fetch from position history/audit
        // For now, return structured explanation format
        
        return [
            'position_id' => $query->positionId,
            'closure_type' => 'target_reached',  // or 'stop_loss', 'manual', 'emergency', 'thesis_invalid'
            'closed_at' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
            
            'summary' => 'Position closed successfully at target price',
            
            'performance' => [
                'entry_price' => 43250.00,
                'exit_price' => 46420.00,
                'return_percent' => 7.33,
                'return_amount' => 634.00,
                'duration_hours' => 72,
                'fees_paid' => 15.24
            ],
            
            'closure_reasoning' => [
                'trigger' => 'Price reached 99% of target',
                'market_conditions' => [
                    'Strong resistance at $46,500 detected',
                    'Volume declining near target',
                    'RSI entering overbought territory (72)'
                ],
                'risk_factors' => [
                    'Potential reversal signals appearing',
                    'Profit protection priority'
                ]
            ],
            
            'timeline' => [
                [
                    'timestamp' => '2025-12-13T10:30:00Z',
                    'event' => 'Position opened',
                    'price' => 43250.00
                ],
                [
                    'timestamp' => '2025-12-14T08:15:00Z',
                    'event' => 'First target hit (50%)',
                    'price' => 44875.00
                ],
                [
                    'timestamp' => '2025-12-15T14:20:00Z',
                    'event' => 'Approach warning to target',
                    'price' => 46100.00
                ],
                [
                    'timestamp' => '2025-12-16T10:30:00Z',
                    'event' => 'Position closed at target',
                    'price' => 46420.00
                ]
            ],
            
            'thesis_validation' => [
                'expected_outcome' => 'Price increase to $46,500',
                'actual_outcome' => 'Price reached $46,420',
                'thesis_accuracy' => 0.98,
                'notes' => 'Thesis validated - technical setup played out as expected'
            ]
        ];
    }
}
