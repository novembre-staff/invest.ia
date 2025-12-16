<?php

declare(strict_types=1);

namespace App\Identity\Domain\Event;

use App\Identity\Domain\ValueObject\Email;
use App\Identity\Domain\ValueObject\UserId;
use App\Identity\Domain\ValueObject\UserPreferences;

final readonly class UserPreferencesUpdated
{
    public function __construct(
        public UserId $userId,
        public Email $email,
        public UserPreferences $preferences,
        public \DateTimeImmutable $occurredAt
    ) {}
}
