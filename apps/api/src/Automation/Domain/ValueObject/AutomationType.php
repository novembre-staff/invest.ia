<?php

declare(strict_types=1);

namespace App\Automation\Domain\ValueObject;

enum AutomationType: string
{
    case DCA = 'dca'; // Dollar Cost Averaging
    case GRID_TRADING = 'grid_trading';
    case REBALANCING = 'rebalancing';
    case STOP_LOSS_TRAILING = 'stop_loss_trailing';
    case TAKE_PROFIT_LADDER = 'take_profit_ladder';
    case ARBITRAGE = 'arbitrage';
    case CUSTOM_SCRIPT = 'custom_script';

    public function getDescription(): string
    {
        return match ($this) {
            self::DCA => 'Dollar Cost Averaging - Achats récurrents à intervalles réguliers',
            self::GRID_TRADING => 'Grid Trading - Ordres d\'achat/vente espacés en grille',
            self::REBALANCING => 'Rebalancing - Rééquilibrage automatique du portfolio',
            self::STOP_LOSS_TRAILING => 'Trailing Stop Loss - Stop loss suivant le prix',
            self::TAKE_PROFIT_LADDER => 'Take Profit Ladder - Prises de bénéfices échelonnées',
            self::ARBITRAGE => 'Arbitrage - Exploitation des écarts de prix entre exchanges',
            self::CUSTOM_SCRIPT => 'Script personnalisé - Logique d\'automatisation sur mesure',
        };
    }

    public function requiresInterval(): bool
    {
        return match ($this) {
            self::DCA, self::REBALANCING => true,
            default => false,
        };
    }

    public function requiresPriceGrid(): bool
    {
        return $this === self::GRID_TRADING;
    }
}
