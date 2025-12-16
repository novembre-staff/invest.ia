<?php

declare(strict_types=1);

namespace App\Identity\Domain\Event;

use App\Identity\Domain\ValueObject\UserId;

final class UserEmailVerified
{
    public function __construct(
        public readonly UserId $userId,
        public readonly \DateTimeImmutable $occurredAt
    ) {}
}
