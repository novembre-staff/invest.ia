<?php

declare(strict_types=1);

namespace App\Bots\Domain\Repository;

use App\Bots\Domain\Model\Proposal;
use App\Bots\Domain\ValueObject\ProposalId;
use App\Bots\Domain\ValueObject\ProposalStatus;
use App\Identity\Domain\ValueObject\UserId;
use App\Strategy\Domain\ValueObject\StrategyId;

interface ProposalRepositoryInterface
{
    public function save(Proposal $proposal): void;
    
    public function findById(ProposalId $id): ?Proposal;
    
    /**
     * @return Proposal[]
     */
    public function findByUserId(UserId $userId, ?ProposalStatus $status = null, int $limit = 50): array;
    
    /**
     * @return Proposal[]
     */
    public function findByStrategyId(StrategyId $strategyId, ?ProposalStatus $status = null): array;
    
    /**
     * Trouver les propositions pendantes expirées
     * 
     * @return Proposal[]
     */
    public function findExpiredPendingProposals(): array;
    
    /**
     * Compter les propositions pendantes pour un utilisateur
     */
    public function countPendingByUserId(UserId $userId): int;
    
    public function delete(Proposal $proposal): void;
}
