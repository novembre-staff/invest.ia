<?php

declare(strict_types=1);

namespace App\Identity\Domain\ValueObject;

final class HashedPassword
{
    private string $hash;
    
    private function __construct(string $hash)
    {
        $this->hash = $hash;
    }
    
    public static function fromPlainPassword(string $plainPassword): self
    {
        if (strlen($plainPassword) < 8) {
            throw new \InvalidArgumentException('Password must be at least 8 characters long');
        }
        
        if (strlen($plainPassword) > 72) {
            throw new \InvalidArgumentException('Password must not exceed 72 characters (bcrypt limitation)');
        }
        
        // Validate password strength
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/', $plainPassword)) {
            throw new \InvalidArgumentException(
                'Password must contain at least one lowercase letter, one uppercase letter, and one digit'
            );
        }
        
        return new self($plainPassword);
    }
    
    public static function fromHash(string $hash): self
    {
        if (empty($hash)) {
            throw new \InvalidArgumentException('Hash cannot be empty');
        }
        
        return new self($hash);
    }
    
    public function getHash(): string
    {
        return $this->hash;
    }
    
    public function __toString(): string
    {
        return '[PROTECTED]';
    }
}
