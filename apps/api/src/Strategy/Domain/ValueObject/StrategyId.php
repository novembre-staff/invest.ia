<?php

declare(strict_types=1);

namespace App\Strategy\Domain\ValueObject;

use Ramsey\Uuid\Uuid;

final readonly class StrategyId
{
    private function __construct(
        private string $value
    ) {
    }

    public static function generate(): self
    {
        return new self(Uuid::uuid4()->toString());
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(StrategyId $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
