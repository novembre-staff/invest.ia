<?php

declare(strict_types=1);

namespace App\Bots\Domain\ValueObject;

use InvalidArgumentException;

/**
 * Type de stratégie de rebalancing automatique
 */
final class RebalancingStrategy
{
    public const PERIODIC = 'periodic';           // Rebalancing périodique (ex: mensuel)
    public const THRESHOLD = 'threshold';         // Rebalancing si déviation > seuil
    public const DRIFT = 'drift';                 // Rebalancing si drift > X%
    public const NONE = 'none';                   // Pas de rebalancing auto

    private function __construct(
        private readonly string $value
    ) {
    }

    public static function fromString(string $value): self
    {
        if (!in_array($value, self::validStrategies(), true)) {
            throw new InvalidArgumentException(
                sprintf('Invalid rebalancing strategy: %s', $value)
            );
        }

        return new self($value);
    }

    public static function periodic(): self
    {
        return new self(self::PERIODIC);
    }

    public static function threshold(): self
    {
        return new self(self::THRESHOLD);
    }

    public static function drift(): self
    {
        return new self(self::DRIFT);
    }

    public static function none(): self
    {
        return new self(self::NONE);
    }

    /**
     * @return string[]
     */
    public static function validStrategies(): array
    {
        return [self::PERIODIC, self::THRESHOLD, self::DRIFT, self::NONE];
    }

    public function value(): string
    {
        return $this->value;
    }

    public function isEnabled(): bool
    {
        return $this->value !== self::NONE;
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
