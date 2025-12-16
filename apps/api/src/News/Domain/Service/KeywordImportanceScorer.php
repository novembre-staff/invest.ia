<?php

declare(strict_types=1);

namespace App\News\Domain\Service;

use App\News\Domain\ValueObject\ImportanceScore;

/**
 * Simple keyword-based importance scorer
 */
final class KeywordImportanceScorer implements ImportanceScorerInterface
{
    private const HIGH_IMPACT_KEYWORDS = [
        'crash', 'collapse', 'hack', 'hacked', 'security breach', 'exploit',
        'regulation', 'ban', 'lawsuit', 'sec', 'investigation',
        'partnership', 'acquisition', 'merger', 'ipo', 'listing',
        'halted', 'suspended', 'delisted',
        'breakthrough', 'revolutionary', 'game-changer',
        'bull run', 'bear market', 'all-time high', 'ath',
        'critical', 'urgent', 'breaking', 'alert'
    ];

    private const MEDIUM_IMPACT_KEYWORDS = [
        'update', 'upgrade', 'launch', 'release',
        'price', 'surge', 'drop', 'rally', 'dump',
        'volume', 'trend', 'analysis',
        'bitcoin', 'ethereum', 'btc', 'eth',
        'earnings', 'revenue', 'profit', 'loss'
    ];

    private const TRUSTED_SOURCES = [
        'CoinDesk', 'CoinTelegraph', 'Bloomberg', 'Reuters', 'CNBC',
        'The Block', 'Decrypt', 'CryptoSlate', 'Bitcoin Magazine'
    ];

    public function calculateScore(
        string $title,
        string $summary,
        string $sourceName,
        array $relatedSymbols,
        \DateTimeImmutable $publishedAt
    ): ImportanceScore {
        $score = 40; // Base score

        $textLower = strtolower($title . ' ' . $summary);

        // Check high impact keywords (+20 each, max +40)
        $highImpactCount = 0;
        foreach (self::HIGH_IMPACT_KEYWORDS as $keyword) {
            if (str_contains($textLower, $keyword)) {
                $highImpactCount++;
            }
        }
        $score += min($highImpactCount * 20, 40);

        // Check medium impact keywords (+5 each, max +15)
        $mediumImpactCount = 0;
        foreach (self::MEDIUM_IMPACT_KEYWORDS as $keyword) {
            if (str_contains($textLower, $keyword)) {
                $mediumImpactCount++;
            }
        }
        $score += min($mediumImpactCount * 5, 15);

        // Trusted source bonus (+15)
        foreach (self::TRUSTED_SOURCES as $trustedSource) {
            if (stripos($sourceName, $trustedSource) !== false) {
                $score += 15;
                break;
            }
        }

        // Related symbols bonus (+5 per symbol, max +15)
        $score += min(count($relatedSymbols) * 5, 15);

        // Recency bonus (up to +10 for very recent news)
        $now = new \DateTimeImmutable();
        $ageInHours = ($now->getTimestamp() - $publishedAt->getTimestamp()) / 3600;
        
        if ($ageInHours < 1) {
            $score += 10;
        } elseif ($ageInHours < 6) {
            $score += 5;
        } elseif ($ageInHours < 24) {
            $score += 2;
        }

        // Cap at 100
        $score = min($score, 100);

        return ImportanceScore::fromInt((int)$score);
    }
}
