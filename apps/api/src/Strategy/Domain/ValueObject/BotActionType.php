<?php

declare(strict_types=1);

namespace App\Strategy\Domain\ValueObject;

/**
 * Actions qu'un bot peut proposer sur une position existante
 */
enum BotActionType: string
{
    case EXIT = 'exit';           // Sortir complètement de la position
    case REDUCE = 'reduce';       // Réduire la taille de la position
    case HOLD = 'hold';          // Ne rien faire (tenir la position)
    case INCREASE = 'increase';   // Augmenter la position (future)
    
    public function getDisplayName(): string
    {
        return match($this) {
            self::EXIT => 'Exit Position',
            self::REDUCE => 'Reduce Position',
            self::HOLD => 'Hold Position',
            self::INCREASE => 'Increase Position',
        };
    }

    public function requiresApproval(): bool
    {
        return match($this) {
            self::EXIT, self::REDUCE, self::INCREASE => true,
            self::HOLD => false,
        };
    }

    public function getIcon(): string
    {
        return match($this) {
            self::EXIT => '🚪',
            self::REDUCE => '📉',
            self::HOLD => '⏸️',
            self::INCREASE => '📈',
        };
    }
}
