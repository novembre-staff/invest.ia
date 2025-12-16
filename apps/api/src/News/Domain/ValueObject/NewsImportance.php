<?php

declare(strict_types=1);

namespace App\News\Domain\ValueObject;

use InvalidArgumentException;

final class NewsImportance
{
    public const LOW = 'low';
    public const MEDIUM = 'medium';
    public const HIGH = 'high';
    public const CRITICAL = 'critical';

    private function __construct(
        private readonly string $value
    ) {
    }

    public static function fromString(string $value): self
    {
        if (!in_array($value, [self::LOW, self::MEDIUM, self::HIGH, self::CRITICAL], true)) {
            throw new InvalidArgumentException(
                sprintf('Invalid importance level: %s', $value)
            );
        }

        return new self($value);
    }

    public static function low(): self
    {
        return new self(self::LOW);
    }

    public static function medium(): self
    {
        return new self(self::MEDIUM);
    }

    public static function high(): self
    {
        return new self(self::HIGH);
    }

    public static function critical(): self
    {
        return new self(self::CRITICAL);
    }

    /**
     * Calcule l'importance basée sur plusieurs critères
     */
    public static function calculate(
        SentimentScore $sentiment,
        int $sourceReliability,
        bool $mentionsWatchedAssets,
        bool $hasMarketImpact
    ): self {
        $score = 0;

        // Sentiment extrême +2 points
        if ($sentiment->isExtreme()) {
            $score += 2;
        } elseif (!$sentiment->isNeutral()) {
            $score += 1;
        }

        // Source fiable +1 point
        if ($sourceReliability >= 8) {
            $score += 1;
        }

        // Mentionne des actifs suivis +2 points
        if ($mentionsWatchedAssets) {
            $score += 2;
        }

        // Impact marché détecté +2 points
        if ($hasMarketImpact) {
            $score += 2;
        }

        return match (true) {
            $score >= 6 => self::critical(),
            $score >= 4 => self::high(),
            $score >= 2 => self::medium(),
            default => self::low()
        };
    }

    public function value(): string
    {
        return $this->value;
    }

    public function isCritical(): bool
    {
        return $this->value === self::CRITICAL;
    }

    public function shouldAlert(): bool
    {
        return in_array($this->value, [self::HIGH, self::CRITICAL], true);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
