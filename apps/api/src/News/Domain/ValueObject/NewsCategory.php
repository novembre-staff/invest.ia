<?php

declare(strict_types=1);

namespace App\News\Domain\ValueObject;

/**
 * News category (Crypto, Finance, Technology, etc.)
 */
enum NewsCategory: string
{
    case CRYPTO = 'crypto';
    case FINANCE = 'finance';
    case TECHNOLOGY = 'technology';
    case REGULATION = 'regulation';
    case MARKET_ANALYSIS = 'market_analysis';
    case COMPANY_NEWS = 'company_news';
    case GENERAL = 'general';

    public function getDisplayName(): string
    {
        return match($this) {
            self::CRYPTO => 'Cryptocurrency',
            self::FINANCE => 'Finance',
            self::TECHNOLOGY => 'Technology',
            self::REGULATION => 'Regulation',
            self::MARKET_ANALYSIS => 'Market Analysis',
            self::COMPANY_NEWS => 'Company News',
            self::GENERAL => 'General',
        };
    }
}
