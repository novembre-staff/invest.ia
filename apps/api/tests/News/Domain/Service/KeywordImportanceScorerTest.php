<?php

declare(strict_types=1);

namespace App\Tests\News\Domain\Service;

use App\News\Domain\Service\KeywordImportanceScorer;
use App\News\Domain\ValueObject\ImportanceScore;
use PHPUnit\Framework\TestCase;

class KeywordImportanceScorerTest extends TestCase
{
    private KeywordImportanceScorer $scorer;

    protected function setUp(): void
    {
        $this->scorer = new KeywordImportanceScorer();
    }

    public function test_calculates_base_score_for_generic_news(): void
    {
        $score = $this->scorer->calculateScore(
            'Bitcoin price update',
            'Bitcoin price has moved slightly today.',
            'Generic News Site',
            [],
            new \DateTimeImmutable('-2 hours')
        );

        $this->assertInstanceOf(ImportanceScore::class, $score);
        // Base score is 40, no high-impact keywords, medium keywords (update, price, bitcoin)
        // should give some bonus
        $this->assertGreaterThanOrEqual(40, $score->getValue());
        $this->assertLessThan(70, $score->getValue()); // Should not be high
    }

    public function test_high_impact_keywords_increase_score(): void
    {
        $score = $this->scorer->calculateScore(
            'BREAKING: Major exchange hack reported',
            'A critical security breach has been detected.',
            'CryptoNews',
            ['BTC', 'ETH'],
            new \DateTimeImmutable('-30 minutes')
        );

        // "hack" and "critical" are high-impact keywords
        // Recent news (<1h) gets recency bonus
        // Multiple symbols get bonus
        $this->assertGreaterThanOrEqual(75, $score->getValue());
        $this->assertTrue($score->shouldAlert());
    }

    public function test_trusted_source_increases_score(): void
    {
        $normalScore = $this->scorer->calculateScore(
            'Bitcoin analysis',
            'An analysis of Bitcoin trends.',
            'Unknown Blog',
            [],
            new \DateTimeImmutable('-1 day')
        );

        $trustedScore = $this->scorer->calculateScore(
            'Bitcoin analysis',
            'An analysis of Bitcoin trends.',
            'CoinDesk', // Trusted source
            [],
            new \DateTimeImmutable('-1 day')
        );

        $this->assertGreaterThan($normalScore->getValue(), $trustedScore->getValue());
    }

    public function test_recency_increases_score(): void
    {
        $oldScore = $this->scorer->calculateScore(
            'Market update',
            'The market has moved.',
            'News Site',
            [],
            new \DateTimeImmutable('-2 days')
        );

        $recentScore = $this->scorer->calculateScore(
            'Market update',
            'The market has moved.',
            'News Site',
            [],
            new \DateTimeImmutable('-15 minutes')
        );

        $this->assertGreaterThan($oldScore->getValue(), $recentScore->getValue());
    }

    public function test_related_symbols_increase_score(): void
    {
        $noSymbolsScore = $this->scorer->calculateScore(
            'Crypto news',
            'General crypto market news.',
            'News Site',
            [],
            new \DateTimeImmutable('-1 hour')
        );

        $withSymbolsScore = $this->scorer->calculateScore(
            'Crypto news',
            'General crypto market news.',
            'News Site',
            ['BTC', 'ETH', 'USDT'],
            new \DateTimeImmutable('-1 hour')
        );

        $this->assertGreaterThan($noSymbolsScore->getValue(), $withSymbolsScore->getValue());
    }

    public function test_multiple_high_impact_keywords(): void
    {
        $score = $this->scorer->calculateScore(
            'SEC investigation into exchange hack leading to market crash',
            'Regulatory action following security breach.',
            'Bloomberg', // Trusted
            ['BTC'],
            new \DateTimeImmutable('-5 minutes')
        );

        // Multiple high-impact keywords (SEC, investigation, hack, crash)
        // Trusted source, recent, symbol
        $this->assertGreaterThanOrEqual(85, $score->getValue());
        $this->assertTrue($score->isCritical());
    }

    public function test_score_capped_at_100(): void
    {
        $score = $this->scorer->calculateScore(
            'URGENT BREAKING ALERT: Critical security breach hack exploit crash suspended',
            'SEC investigation lawsuit regulation ban halted.',
            'CoinDesk',
            ['BTC', 'ETH', 'USDT', 'BNB'],
            new \DateTimeImmutable('now')
        );

        $this->assertLessThanOrEqual(100, $score->getValue());
        $this->assertEquals(100, $score->getValue());
    }

    public function test_case_insensitive_keyword_matching(): void
    {
        $lowerScore = $this->scorer->calculateScore(
            'hack detected',
            'A hack was found.',
            'News',
            [],
            new \DateTimeImmutable('-1 hour')
        );

        $upperScore = $this->scorer->calculateScore(
            'HACK DETECTED',
            'A HACK was found.',
            'News',
            [],
            new \DateTimeImmutable('-1 hour')
        );

        $this->assertEquals($lowerScore->getValue(), $upperScore->getValue());
    }

    public function test_regulation_news_scores_high(): void
    {
        $score = $this->scorer->calculateScore(
            'New crypto regulation announced by SEC',
            'The SEC has announced new cryptocurrency regulations that will impact the market.',
            'Reuters',
            ['BTC', 'ETH'],
            new \DateTimeImmutable('-20 minutes')
        );

        // "regulation" and "SEC" are high-impact
        // Trusted source (Reuters)
        // Recent
        // Multiple symbols
        $this->assertGreaterThanOrEqual(75, $score->getValue());
        $this->assertTrue($score->shouldAlert());
    }

    public function test_partnership_announcement_scores_high(): void
    {
        $score = $this->scorer->calculateScore(
            'Major partnership announced between crypto platforms',
            'A groundbreaking partnership has been revealed.',
            'CoinTelegraph',
            ['BTC'],
            new \DateTimeImmutable('-10 minutes')
        );

        // "partnership" is high-impact
        $this->assertGreaterThanOrEqual(65, $score->getValue());
    }
}
