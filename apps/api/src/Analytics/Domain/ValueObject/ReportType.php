<?php

declare(strict_types=1);

namespace App\Analytics\Domain\ValueObject;

enum ReportType: string
{
    case PORTFOLIO_PERFORMANCE = 'portfolio_performance';
    case TRADING_SUMMARY = 'trading_summary';
    case ASSET_ALLOCATION = 'asset_allocation';
    case PROFIT_LOSS = 'profit_loss';
    case RISK_ANALYSIS = 'risk_analysis';
    case TAX_REPORT = 'tax_report';

    public function getDescription(): string
    {
        return match ($this) {
            self::PORTFOLIO_PERFORMANCE => 'Performance globale du portfolio',
            self::TRADING_SUMMARY => 'Résumé des activités de trading',
            self::ASSET_ALLOCATION => 'Répartition des actifs',
            self::PROFIT_LOSS => 'Gains et pertes détaillés',
            self::RISK_ANALYSIS => 'Analyse des risques',
            self::TAX_REPORT => 'Rapport fiscal',
        };
    }
}
