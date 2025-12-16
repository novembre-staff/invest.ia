<?php

declare(strict_types=1);

namespace App\Bots\Application\DTO;

use App\Bots\Domain\Model\Proposal;

final readonly class ProposalDTO
{
    /**
     * @param array<string, mixed> $riskFactors
     */
    public function __construct(
        public string $id,
        public string $userId,
        public string $strategyId,
        public string $symbol,
        public string $side,
        public string $quantity,
        public ?string $limitPrice,
        public string $status,
        public string $rationale,
        public array $riskFactors,
        public string $riskScore,
        public ?string $expectedReturn,
        public ?string $stopLoss,
        public ?string $takeProfit,
        public string $createdAt,
        public string $expiresAt,
        public ?string $respondedAt,
        public ?string $orderId,
        public int $timeToExpiration
    ) {}
    
    public static function fromAggregate(Proposal $proposal): self
    {
        return new self(
            id: $proposal->getId()->getValue(),
            userId: $proposal->getUserId()->getValue(),
            strategyId: $proposal->getStrategyId()->getValue(),
            symbol: $proposal->getSymbol(),
            side: $proposal->getSide(),
            quantity: $proposal->getQuantity(),
            limitPrice: $proposal->getLimitPrice(),
            status: $proposal->getStatus()->value,
            rationale: $proposal->getRationale(),
            riskFactors: $proposal->getRiskFactors(),
            riskScore: $proposal->getRiskScore(),
            expectedReturn: $proposal->getExpectedReturn(),
            stopLoss: $proposal->getStopLoss(),
            takeProfit: $proposal->getTakeProfit(),
            createdAt: $proposal->getCreatedAt()->format(\DateTimeInterface::ATOM),
            expiresAt: $proposal->getExpiresAt()->format(\DateTimeInterface::ATOM),
            respondedAt: $proposal->getRespondedAt()?->format(\DateTimeInterface::ATOM),
            orderId: $proposal->getOrderId(),
            timeToExpiration: $proposal->getTimeToExpiration()
        );
    }
}
