<?php

declare(strict_types=1);

namespace App\Identity\Domain\ValueObject;

enum UserStatus: string
{
    case PENDING_VERIFICATION = 'pending_verification';
    case ACTIVE = 'active';
    case SUSPENDED = 'suspended';
    case DELETED = 'deleted';
    
    public function isPendingVerification(): bool
    {
        return $this === self::PENDING_VERIFICATION;
    }
    
    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }
    
    public function isSuspended(): bool
    {
        return $this === self::SUSPENDED;
    }
    
    public function isDeleted(): bool
    {
        return $this === self::DELETED;
    }
}
