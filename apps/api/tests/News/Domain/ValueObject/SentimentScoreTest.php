<?php

declare(strict_types=1);

namespace App\Tests\News\Domain\ValueObject;

use App\News\Domain\ValueObject\SentimentScore;
use PHPUnit\Framework\TestCase;

final class SentimentScoreTest extends TestCase
{
    public function testCreateValidSentimentScore(): void
    {
        $score = SentimentScore::fromScore(0.5);
        
        $this->assertEquals(0.5, $score->score());
        $this->assertEquals('positive', $score->label());
        $this->assertTrue($score->isPositive());
        $this->assertFalse($score->isNegative());
        $this->assertFalse($score->isNeutral());
        $this->assertFalse($score->isExtreme());
    }

    public function testVeryPositiveSentiment(): void
    {
        $score = SentimentScore::fromScore(0.8);
        
        $this->assertEquals('very_positive', $score->label());
        $this->assertTrue($score->isPositive());
        $this->assertTrue($score->isExtreme());
    }

    public function testVeryNegativeSentiment(): void
    {
        $score = SentimentScore::fromScore(-0.8);
        
        $this->assertEquals('very_negative', $score->label());
        $this->assertTrue($score->isNegative());
        $this->assertTrue($score->isExtreme());
    }

    public function testNeutralSentiment(): void
    {
        $score = SentimentScore::fromScore(0.0);
        
        $this->assertEquals('neutral', $score->label());
        $this->assertTrue($score->isNeutral());
        $this->assertFalse($score->isPositive());
        $this->assertFalse($score->isNegative());
        $this->assertFalse($score->isExtreme());
    }

    public function testNegativeSentiment(): void
    {
        $score = SentimentScore::fromScore(-0.4);
        
        $this->assertEquals('negative', $score->label());
        $this->assertTrue($score->isNegative());
        $this->assertFalse($score->isExtreme());
    }

    public function testScoreTooHigh(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Sentiment score must be between -1.0 and 1.0');
        
        SentimentScore::fromScore(1.5);
    }

    public function testScoreTooLow(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Sentiment score must be between -1.0 and 1.0');
        
        SentimentScore::fromScore(-1.5);
    }

    public function testEquals(): void
    {
        $score1 = SentimentScore::fromScore(0.5);
        $score2 = SentimentScore::fromScore(0.505);
        $score3 = SentimentScore::fromScore(0.6);
        
        $this->assertTrue($score1->equals($score2)); // Différence < 0.01
        $this->assertFalse($score1->equals($score3));
    }

    public function testBoundaryValues(): void
    {
        $min = SentimentScore::fromScore(-1.0);
        $max = SentimentScore::fromScore(1.0);
        
        $this->assertEquals(-1.0, $min->score());
        $this->assertEquals(1.0, $max->score());
        $this->assertEquals('very_negative', $min->label());
        $this->assertEquals('very_positive', $max->label());
    }
}
