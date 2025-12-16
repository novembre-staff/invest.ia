<?php

declare(strict_types=1);

namespace App\News\Domain\ValueObject;

/**
 * Sentiment analysis result for news articles
 * Represents the emotional tone and market impact of news
 */
enum NewsSentiment: string
{
    case VERY_POSITIVE = 'very_positive';
    case POSITIVE = 'positive';
    case NEUTRAL = 'neutral';
    case NEGATIVE = 'negative';
    case VERY_NEGATIVE = 'very_negative';

    /**
     * Get numeric score (-1.0 to 1.0)
     */
    public function getScore(): float
    {
        return match ($this) {
            self::VERY_POSITIVE => 1.0,
            self::POSITIVE => 0.5,
            self::NEUTRAL => 0.0,
            self::NEGATIVE => -0.5,
            self::VERY_NEGATIVE => -1.0,
        };
    }

    /**
     * Check if sentiment is bullish
     */
    public function isBullish(): bool
    {
        return in_array($this, [self::VERY_POSITIVE, self::POSITIVE]);
    }

    /**
     * Check if sentiment is bearish
     */
    public function isBearish(): bool
    {
        return in_array($this, [self::VERY_NEGATIVE, self::NEGATIVE]);
    }

    /**
     * Get display label
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::VERY_POSITIVE => 'Very Bullish',
            self::POSITIVE => 'Bullish',
            self::NEUTRAL => 'Neutral',
            self::NEGATIVE => 'Bearish',
            self::VERY_NEGATIVE => 'Very Bearish',
        };
    }

    /**
     * Get emoji representation
     */
    public function getEmoji(): string
    {
        return match ($this) {
            self::VERY_POSITIVE => '🚀',
            self::POSITIVE => '📈',
            self::NEUTRAL => '➡️',
            self::NEGATIVE => '📉',
            self::VERY_NEGATIVE => '⚠️',
        };
    }

    /**
     * Create from numeric score (-1.0 to 1.0)
     */
    public static function fromScore(float $score): self
    {
        return match (true) {
            $score >= 0.7 => self::VERY_POSITIVE,
            $score >= 0.2 => self::POSITIVE,
            $score >= -0.2 => self::NEUTRAL,
            $score >= -0.7 => self::NEGATIVE,
            default => self::VERY_NEGATIVE,
        };
    }
}
