<?php

declare(strict_types=1);

namespace App\Tests\News\Domain\ValueObject;

use App\News\Domain\ValueObject\NewsImportance;
use App\News\Domain\ValueObject\SentimentScore;
use PHPUnit\Framework\TestCase;

final class NewsImportanceTest extends TestCase
{
    public function testCreateFromString(): void
    {
        $importance = NewsImportance::fromString('high');
        
        $this->assertEquals('high', $importance->value());
        $this->assertTrue($importance->shouldAlert());
    }

    public function testNamedConstructors(): void
    {
        $this->assertEquals('low', NewsImportance::low()->value());
        $this->assertEquals('medium', NewsImportance::medium()->value());
        $this->assertEquals('high', NewsImportance::high()->value());
        $this->assertEquals('critical', NewsImportance::critical()->value());
    }

    public function testInvalidImportance(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid importance level: invalid');
        
        NewsImportance::fromString('invalid');
    }

    public function testIsCritical(): void
    {
        $this->assertTrue(NewsImportance::critical()->isCritical());
        $this->assertFalse(NewsImportance::high()->isCritical());
        $this->assertFalse(NewsImportance::medium()->isCritical());
        $this->assertFalse(NewsImportance::low()->isCritical());
    }

    public function testShouldAlert(): void
    {
        $this->assertTrue(NewsImportance::critical()->shouldAlert());
        $this->assertTrue(NewsImportance::high()->shouldAlert());
        $this->assertFalse(NewsImportance::medium()->shouldAlert());
        $this->assertFalse(NewsImportance::low()->shouldAlert());
    }

    public function testCalculateImportanceLow(): void
    {
        $sentiment = SentimentScore::fromScore(0.1);
        
        $importance = NewsImportance::calculate(
            sentiment: $sentiment,
            sourceReliability: 5,
            mentionsWatchedAssets: false,
            hasMarketImpact: false
        );
        
        $this->assertEquals('low', $importance->value());
    }

    public function testCalculateImportanceMedium(): void
    {
        $sentiment = SentimentScore::fromScore(0.4);
        
        $importance = NewsImportance::calculate(
            sentiment: $sentiment,
            sourceReliability: 8,
            mentionsWatchedAssets: false,
            hasMarketImpact: false
        );
        
        $this->assertEquals('medium', $importance->value());
    }

    public function testCalculateImportanceHigh(): void
    {
        $sentiment = SentimentScore::fromScore(0.5);
        
        $importance = NewsImportance::calculate(
            sentiment: $sentiment,
            sourceReliability: 8,
            mentionsWatchedAssets: true,
            hasMarketImpact: false
        );
        
        $this->assertEquals('high', $importance->value());
    }

    public function testCalculateImportanceCritical(): void
    {
        $sentiment = SentimentScore::fromScore(-0.8);
        
        $importance = NewsImportance::calculate(
            sentiment: $sentiment,
            sourceReliability: 9,
            mentionsWatchedAssets: true,
            hasMarketImpact: true
        );
        
        $this->assertEquals('critical', $importance->value());
    }

    public function testEquals(): void
    {
        $high1 = NewsImportance::high();
        $high2 = NewsImportance::high();
        $low = NewsImportance::low();
        
        $this->assertTrue($high1->equals($high2));
        $this->assertFalse($high1->equals($low));
    }

    public function testToString(): void
    {
        $importance = NewsImportance::high();
        
        $this->assertEquals('high', (string) $importance);
    }
}
