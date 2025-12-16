<?php

declare(strict_types=1);

namespace App\Bots\Domain\ValueObject;

use Symfony\Component\Uid\Uuid;

/**
 * Value Object représentant l'identifiant unique d'une proposition
 */
final readonly class ProposalId
{
    private function __construct(
        private string $value
    ) {
        if (!Uuid::isValid($value)) {
            throw new \InvalidArgumentException('Invalid Proposal ID format');
        }
    }
    
    public static function generate(): self
    {
        return new self(Uuid::v4()->toRfc4122());
    }
    
    public static function fromString(string $id): self
    {
        return new self($id);
    }
    
    public function getValue(): string
    {
        return $this->value;
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
