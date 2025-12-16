<?php

declare(strict_types=1);

namespace App\Tests\Bots\Domain\Model;

use App\Bots\Domain\Model\Proposal;
use App\Bots\Domain\ValueObject\ProposalId;
use App\Bots\Domain\ValueObject\ProposalStatus;
use App\Identity\Domain\ValueObject\UserId;
use App\Strategy\Domain\ValueObject\StrategyId;
use PHPUnit\Framework\TestCase;

class ProposalTest extends TestCase
{
    public function testCreateProposal(): void
    {
        $proposal = new Proposal(
            ProposalId::generate(),
            UserId::generate(),
            StrategyId::generate(),
            'BTCUSDT',
            'buy',
            '0.1',
            'Strong bullish momentum detected',
            ['market_volatility' => 'medium', 'liquidity' => 'high'],
            'MEDIUM',
            '50000.00',
            '5.0',
            '48000.00',
            '52000.00',
            30
        );

        $this->assertEquals('BTCUSDT', $proposal->getSymbol());
        $this->assertEquals('buy', $proposal->getSide());
        $this->assertEquals('0.1', $proposal->getQuantity());
        $this->assertTrue($proposal->getStatus()->isPending());
        $this->assertEquals('MEDIUM', $proposal->getRiskScore());
        $this->assertFalse($proposal->isExpired());
    }

    public function testAcceptProposal(): void
    {
        $proposal = new Proposal(
            ProposalId::generate(),
            UserId::generate(),
            StrategyId::generate(),
            'ETHUSDT',
            'buy',
            '1.0',
            'Good entry point',
            [],
            'LOW'
        );

        $proposal->accept();

        $this->assertTrue($proposal->getStatus()->isAccepted());
        $this->assertNotNull($proposal->getRespondedAt());
    }

    public function testRejectProposal(): void
    {
        $proposal = new Proposal(
            ProposalId::generate(),
            UserId::generate(),
            StrategyId::generate(),
            'ETHUSDT',
            'sell',
            '1.0',
            'Exit signal',
            [],
            'LOW'
        );

        $proposal->reject('Not interested');

        $this->assertTrue($proposal->getStatus()->isRejected());
        $this->assertNotNull($proposal->getRespondedAt());
    }

    public function testCannotAcceptTwice(): void
    {
        $proposal = new Proposal(
            ProposalId::generate(),
            UserId::generate(),
            StrategyId::generate(),
            'BTCUSDT',
            'buy',
            '0.1',
            'Test',
            [],
            'LOW'
        );

        $proposal->accept();

        $this->expectException(\DomainException::class);
        $proposal->accept();
    }

    public function testExpireProposal(): void
    {
        $proposal = new Proposal(
            ProposalId::generate(),
            UserId::generate(),
            StrategyId::generate(),
            'BTCUSDT',
            'buy',
            '0.1',
            'Test',
            [],
            'LOW',
            null,
            null,
            null,
            null,
            0 // Expire immédiatement
        );

        $this->assertTrue($proposal->isExpired());

        $proposal->expire();
        $this->assertTrue($proposal->getStatus()->isExpired());
    }

    public function testMarkAsExecuted(): void
    {
        $proposal = new Proposal(
            ProposalId::generate(),
            UserId::generate(),
            StrategyId::generate(),
            'BTCUSDT',
            'buy',
            '0.1',
            'Test',
            [],
            'LOW'
        );

        $proposal->accept();
        $proposal->markAsExecuted('order-123');

        $this->assertTrue($proposal->getStatus()->isExecuted());
        $this->assertEquals('order-123', $proposal->getOrderId());
    }

    public function testInvalidSide(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Side must be buy or sell');

        new Proposal(
            ProposalId::generate(),
            UserId::generate(),
            StrategyId::generate(),
            'BTCUSDT',
            'invalid',
            '0.1',
            'Test',
            [],
            'LOW'
        );
    }

    public function testInvalidQuantity(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Quantity must be positive');

        new Proposal(
            ProposalId::generate(),
            UserId::generate(),
            StrategyId::generate(),
            'BTCUSDT',
            'buy',
            '-0.1',
            'Test',
            [],
            'LOW'
        );
    }

    public function testInvalidRiskScore(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Risk score must be LOW, MEDIUM, or HIGH');

        new Proposal(
            ProposalId::generate(),
            UserId::generate(),
            StrategyId::generate(),
            'BTCUSDT',
            'buy',
            '0.1',
            'Test',
            [],
            'INVALID'
        );
    }

    public function testTimeToExpiration(): void
    {
        $proposal = new Proposal(
            ProposalId::generate(),
            UserId::generate(),
            StrategyId::generate(),
            'BTCUSDT',
            'buy',
            '0.1',
            'Test',
            [],
            'LOW',
            null,
            null,
            null,
            null,
            60 // 60 minutes
        );

        $timeToExpiration = $proposal->getTimeToExpiration();
        
        // Devrait être environ 3600 secondes (60 minutes)
        $this->assertGreaterThan(3500, $timeToExpiration);
        $this->assertLessThan(3700, $timeToExpiration);
    }
}
