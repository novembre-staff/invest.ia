<?php

declare(strict_types=1);

namespace App\Identity\Domain\Event;

use App\Identity\Domain\ValueObject\Email;
use App\Identity\Domain\ValueObject\UserId;

final readonly class UserLoggedOut
{
    public function __construct(
        public UserId $userId,
        public Email $email,
        public \DateTimeImmutable $occurredAt
    ) {}
}
