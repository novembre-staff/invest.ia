<?php

declare(strict_types=1);

namespace App\Tests\News\Infrastructure\Service;

use App\News\Domain\ValueObject\SentimentScore;
use App\News\Infrastructure\Service\SimpleSentimentAnalyzer;
use PHPUnit\Framework\TestCase;

final class SimpleSentimentAnalyzerTest extends TestCase
{
    private SimpleSentimentAnalyzer $analyzer;

    protected function setUp(): void
    {
        $this->analyzer = new SimpleSentimentAnalyzer();
    }

    public function testAnalyzePositiveText(): void
    {
        $text = 'Bitcoin surge to new record high as market rally continues with strong momentum';
        
        $sentiment = $this->analyzer->analyze($text);
        
        $this->assertGreaterThan(0, $sentiment->score());
        $this->assertTrue($sentiment->isPositive());
    }

    public function testAnalyzeNegativeText(): void
    {
        $text = 'Crypto crash continues as Bitcoin plunges amid market collapse and widespread panic';
        
        $sentiment = $this->analyzer->analyze($text);
        
        $this->assertLessThan(0, $sentiment->score());
        $this->assertTrue($sentiment->isNegative());
    }

    public function testAnalyzeNeutralText(): void
    {
        $text = 'The current market conditions remain stable with no significant changes';
        
        $sentiment = $this->analyzer->analyze($text);
        
        $this->assertTrue($sentiment->isNeutral());
    }

    public function testAnalyzeWithAmplifiers(): void
    {
        $textWithout = 'Bitcoin rise today';
        $textWith = 'Bitcoin very rise today';
        
        $sentimentWithout = $this->analyzer->analyze($textWithout);
        $sentimentWith = $this->analyzer->analyze($textWith);
        
        // Le texte avec amplificateur devrait avoir un score plus élevé
        $this->assertGreaterThanOrEqual(
            $sentimentWithout->score(),
            $sentimentWith->score()
        );
    }

    public function testAnalyzeBatch(): void
    {
        $texts = [
            'Bitcoin surge to new high',
            'Crypto market crash continues',
            'Stable market conditions today'
        ];
        
        $sentiments = $this->analyzer->analyzeBatch($texts);
        
        $this->assertCount(3, $sentiments);
        $this->assertInstanceOf(SentimentScore::class, $sentiments[0]);
        $this->assertTrue($sentiments[0]->isPositive());
        $this->assertTrue($sentiments[1]->isNegative());
    }

    public function testAnalyzeEmptyText(): void
    {
        $sentiment = $this->analyzer->analyze('');
        
        $this->assertEquals(0.0, $sentiment->score());
        $this->assertTrue($sentiment->isNeutral());
    }

    public function testAnalyzeMixedSentiment(): void
    {
        $text = 'Bitcoin rise but concerns about crash remain amid weak fundamentals';
        
        $sentiment = $this->analyzer->analyze($text);
        
        // Devrait être relativement neutre ou légèrement négatif
        $this->assertLessThan(0.3, abs($sentiment->score()));
    }
}
