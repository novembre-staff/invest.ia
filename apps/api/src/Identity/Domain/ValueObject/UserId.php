<?php

declare(strict_types=1);

namespace App\Identity\Domain\ValueObject;

use Symfony\Component\Uid\Uuid;

final class UserId
{
    private string $value;
    
    private function __construct(string $uuid)
    {
        $this->value = $uuid;
    }
    
    public static function generate(): self
    {
        return new self(Uuid::v4()->toRfc4122());
    }
    
    public static function fromString(string $uuid): self
    {
        if (!Uuid::isValid($uuid)) {
            throw new \InvalidArgumentException(sprintf('Invalid UUID: %s', $uuid));
        }
        
        return new self($uuid);
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
