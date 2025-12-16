<?php

declare(strict_types=1);

namespace App\Identity\Domain\ValueObject;

final class Email
{
    private string $value;
    
    public function __construct(string $email)
    {
        $email = trim($email);
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException(sprintf('Invalid email format: %s', $email));
        }
        
        if (strlen($email) > 255) {
            throw new \InvalidArgumentException('Email must not exceed 255 characters');
        }
        
        $this->value = strtolower($email);
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
