<?php

declare(strict_types=1);

namespace App\Identity\Application\DTO;

final class UserDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $email,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $status,
        public readonly bool $mfaEnabled,
        public readonly bool $emailVerified,
        public readonly ?\DateTimeImmutable $emailVerifiedAt,
        public readonly \DateTimeImmutable $createdAt
    ) {}
}
