<?php

declare(strict_types=1);

namespace App\Bots\Domain\ValueObject;

/**
 * Enum représentant le statut d'une proposition d'investissement
 */
enum ProposalStatus: string
{
    case PENDING = 'pending';        // En attente de validation utilisateur
    case ACCEPTED = 'accepted';      // Acceptée par l'utilisateur
    case REJECTED = 'rejected';      // Refusée par l'utilisateur
    case EXPIRED = 'expired';        // Expirée (timeout)
    case EXECUTED = 'executed';      // Ordre exécuté suite à acceptation
    case CANCELLED = 'cancelled';    // Annulée (bot stopped, etc.)
    
    public function isPending(): bool
    {
        return $this === self::PENDING;
    }
    
    public function isAccepted(): bool
    {
        return $this === self::ACCEPTED;
    }
    
    public function isRejected(): bool
    {
        return $this === self::REJECTED;
    }
    
    public function isExpired(): bool
    {
        return $this === self::EXPIRED;
    }
    
    public function isExecuted(): bool
    {
        return $this === self::EXECUTED;
    }
    
    public function isCancelled(): bool
    {
        return $this === self::CANCELLED;
    }
    
    public function isFinal(): bool
    {
        return in_array($this, [
            self::ACCEPTED,
            self::REJECTED,
            self::EXPIRED,
            self::EXECUTED,
            self::CANCELLED
        ], true);
    }
    
    public function canBeAccepted(): bool
    {
        return $this === self::PENDING;
    }
    
    public function canBeRejected(): bool
    {
        return $this === self::PENDING;
    }
}
