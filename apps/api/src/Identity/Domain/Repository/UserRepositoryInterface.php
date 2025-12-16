<?php

declare(strict_types=1);

namespace App\Identity\Domain\Repository;

use App\Identity\Domain\Model\User;
use App\Identity\Domain\ValueObject\UserId;
use App\Identity\Domain\ValueObject\Email;

interface UserRepositoryInterface
{
    public function save(User $user): void;
    
    public function findById(UserId $id): ?User;
    
    public function findByEmail(Email $email): ?User;
    
    public function emailExists(Email $email): bool;
    
    public function delete(User $user): void;
}
