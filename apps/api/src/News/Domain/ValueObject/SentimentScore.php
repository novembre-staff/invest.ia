<?php

declare(strict_types=1);

namespace App\News\Domain\ValueObject;

use InvalidArgumentException;

final class SentimentScore
{
    private function __construct(
        private readonly float $score,
        private readonly string $label
    ) {
    }

    public static function fromScore(float $score): self
    {
        if ($score < -1.0 || $score > 1.0) {
            throw new InvalidArgumentException(
                'Sentiment score must be between -1.0 and 1.0'
            );
        }

        $label = match (true) {
            $score <= -0.6 => 'very_negative',
            $score <= -0.2 => 'negative',
            $score <= 0.2 => 'neutral',
            $score <= 0.6 => 'positive',
            default => 'very_positive'
        };

        return new self($score, $label);
    }

    public function score(): float
    {
        return $this->score;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function isPositive(): bool
    {
        return $this->score > 0.2;
    }

    public function isNegative(): bool
    {
        return $this->score < -0.2;
    }

    public function isNeutral(): bool
    {
        return !$this->isPositive() && !$this->isNegative();
    }

    public function isExtreme(): bool
    {
        return abs($this->score) > 0.6;
    }

    public function equals(self $other): bool
    {
        return abs($this->score - $other->score) < 0.01;
    }
}
