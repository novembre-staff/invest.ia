<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Security;

use App\Identity\Domain\Repository\UserRepositoryInterface;
use App\Identity\Domain\ValueObject\Email;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {}

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        try {
            $email = new Email($identifier);
            $user = $this->userRepository->findByEmail($email);
            
            if (!$user) {
                throw new UserNotFoundException(sprintf('User with email "%s" not found.', $identifier));
            }
            
            return new SecurityUser(
                $user->getId()->getValue(),
                $user->getEmail()->getValue(),
                $user->getPassword()->getHash(),
                $user->getStatus()->value,
                $user->isMfaEnabled()
            );
            
        } catch (\InvalidArgumentException $e) {
            throw new UserNotFoundException(sprintf('Invalid email format: "%s"', $identifier));
        }
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof SecurityUser) {
            throw new \InvalidArgumentException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return SecurityUser::class === $class || is_subclass_of($class, SecurityUser::class);
    }
}
