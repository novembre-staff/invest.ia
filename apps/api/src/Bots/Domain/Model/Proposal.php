<?php

declare(strict_types=1);

namespace App\Bots\Domain\Model;

use App\Bots\Domain\ValueObject\ProposalId;
use App\Bots\Domain\ValueObject\ProposalStatus;
use App\Identity\Domain\ValueObject\UserId;
use App\Strategy\Domain\ValueObject\StrategyId;

/**
 * Aggregate Root: Proposition d'investissement générée par un bot
 * 
 * Une proposition représente une recommandation d'investissement que le bot
 * fait à l'utilisateur. Elle nécessite une validation explicite avant exécution.
 */
class Proposal
{
    private ProposalId $id;
    private UserId $userId;
    private StrategyId $strategyId;
    private string $symbol;
    private string $side; // 'buy' or 'sell'
    private string $quantity;
    private ?string $limitPrice;
    private ProposalStatus $status;
    
    // Reasoning & Risk
    private string $rationale; // Pourquoi cette proposition
    private array $riskFactors; // JSON: array of risk factors
    private string $riskScore; // LOW, MEDIUM, HIGH
    private ?string $expectedReturn; // % attendu
    private ?string $stopLoss; // Prix de stop loss suggéré
    private ?string $takeProfit; // Prix de take profit suggéré
    
    // Metadata
    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $expiresAt;
    private ?\DateTimeImmutable $respondedAt;
    private ?string $orderId; // Référence à l'ordre créé si accepté
    
    public function __construct(
        ProposalId $id,
        UserId $userId,
        StrategyId $strategyId,
        string $symbol,
        string $side,
        string $quantity,
        string $rationale,
        array $riskFactors,
        string $riskScore,
        ?string $limitPrice = null,
        ?string $expectedReturn = null,
        ?string $stopLoss = null,
        ?string $takeProfit = null,
        int $expirationMinutes = 30
    ) {
        $this->validateInputs($symbol, $side, $quantity, $riskScore);
        
        $this->id = $id;
        $this->userId = $userId;
        $this->strategyId = $strategyId;
        $this->symbol = strtoupper($symbol);
        $this->side = strtolower($side);
        $this->quantity = $quantity;
        $this->limitPrice = $limitPrice;
        $this->status = ProposalStatus::PENDING;
        
        $this->rationale = $rationale;
        $this->riskFactors = $riskFactors;
        $this->riskScore = strtoupper($riskScore);
        $this->expectedReturn = $expectedReturn;
        $this->stopLoss = $stopLoss;
        $this->takeProfit = $takeProfit;
        
        $this->createdAt = new \DateTimeImmutable();
        $this->expiresAt = $this->createdAt->modify("+{$expirationMinutes} minutes");
        $this->respondedAt = null;
        $this->orderId = null;
    }
    
    private function validateInputs(string $symbol, string $side, string $quantity, string $riskScore): void
    {
        if (empty($symbol) || strlen($symbol) > 20) {
            throw new \InvalidArgumentException('Invalid symbol');
        }
        
        if (!in_array(strtolower($side), ['buy', 'sell'], true)) {
            throw new \InvalidArgumentException('Side must be buy or sell');
        }
        
        if (!is_numeric($quantity) || (float)$quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be positive');
        }
        
        if (!in_array(strtoupper($riskScore), ['LOW', 'MEDIUM', 'HIGH'], true)) {
            throw new \InvalidArgumentException('Risk score must be LOW, MEDIUM, or HIGH');
        }
    }
    
    /**
     * Accepter la proposition
     * Déclenche la création d'un ordre
     */
    public function accept(): void
    {
        if (!$this->status->canBeAccepted()) {
            throw new \DomainException(
                sprintf('Cannot accept proposal with status: %s', $this->status->value)
            );
        }
        
        if ($this->isExpired()) {
            throw new \DomainException('Cannot accept expired proposal');
        }
        
        $this->status = ProposalStatus::ACCEPTED;
        $this->respondedAt = new \DateTimeImmutable();
    }
    
    /**
     * Refuser la proposition
     */
    public function reject(string $reason = ''): void
    {
        if (!$this->status->canBeRejected()) {
            throw new \DomainException(
                sprintf('Cannot reject proposal with status: %s', $this->status->value)
            );
        }
        
        $this->status = ProposalStatus::REJECTED;
        $this->respondedAt = new \DateTimeImmutable();
    }
    
    /**
     * Marquer comme expirée
     */
    public function expire(): void
    {
        if (!$this->status->isPending()) {
            throw new \DomainException('Only pending proposals can expire');
        }
        
        $this->status = ProposalStatus::EXPIRED;
        $this->respondedAt = new \DateTimeImmutable();
    }
    
    /**
     * Marquer comme exécutée (ordre créé et exécuté)
     */
    public function markAsExecuted(string $orderId): void
    {
        if (!$this->status->isAccepted()) {
            throw new \DomainException('Only accepted proposals can be marked as executed');
        }
        
        $this->status = ProposalStatus::EXECUTED;
        $this->orderId = $orderId;
    }
    
    /**
     * Annuler la proposition (ex: bot stopped)
     */
    public function cancel(): void
    {
        if ($this->status->isFinal() && !$this->status->isPending()) {
            throw new \DomainException('Cannot cancel finalized proposal');
        }
        
        $this->status = ProposalStatus::CANCELLED;
        $this->respondedAt = new \DateTimeImmutable();
    }
    
    /**
     * Vérifier si la proposition est expirée
     */
    public function isExpired(): bool
    {
        return new \DateTimeImmutable() > $this->expiresAt;
    }
    
    // Getters
    
    public function getId(): ProposalId
    {
        return $this->id;
    }
    
    public function getUserId(): UserId
    {
        return $this->userId;
    }
    
    public function getStrategyId(): StrategyId
    {
        return $this->strategyId;
    }
    
    public function getSymbol(): string
    {
        return $this->symbol;
    }
    
    public function getSide(): string
    {
        return $this->side;
    }
    
    public function getQuantity(): string
    {
        return $this->quantity;
    }
    
    public function getLimitPrice(): ?string
    {
        return $this->limitPrice;
    }
    
    public function getStatus(): ProposalStatus
    {
        return $this->status;
    }
    
    public function getRationale(): string
    {
        return $this->rationale;
    }
    
    public function getRiskFactors(): array
    {
        return $this->riskFactors;
    }
    
    public function getRiskScore(): string
    {
        return $this->riskScore;
    }
    
    public function getExpectedReturn(): ?string
    {
        return $this->expectedReturn;
    }
    
    public function getStopLoss(): ?string
    {
        return $this->stopLoss;
    }
    
    public function getTakeProfit(): ?string
    {
        return $this->takeProfit;
    }
    
    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
    
    public function getExpiresAt(): \DateTimeImmutable
    {
        return $this->expiresAt;
    }
    
    public function getRespondedAt(): ?\DateTimeImmutable
    {
        return $this->respondedAt;
    }
    
    public function getOrderId(): ?string
    {
        return $this->orderId;
    }
    
    /**
     * Calculer le temps restant avant expiration (en secondes)
     */
    public function getTimeToExpiration(): int
    {
        $now = new \DateTimeImmutable();
        $diff = $this->expiresAt->getTimestamp() - $now->getTimestamp();
        return max(0, $diff);
    }
}
