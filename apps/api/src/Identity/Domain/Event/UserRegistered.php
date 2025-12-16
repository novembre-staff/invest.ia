<?php

declare(strict_types=1);

namespace App\Identity\Domain\Event;

use App\Identity\Domain\ValueObject\UserId;
use App\Identity\Domain\ValueObject\Email;

final class UserRegistered
{
    public function __construct(
        public readonly UserId $userId,
        public readonly Email $email,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly \DateTimeImmutable $occurredAt
    ) {}
}
