<?php

declare(strict_types=1);

namespace App\Identity\Application\Handler;

use App\Identity\Application\Command\RegisterUser;
use App\Identity\Application\DTO\UserDTO;
use App\Identity\Domain\Model\User;
use App\Identity\Domain\ValueObject\UserId;
use App\Identity\Domain\ValueObject\Email;
use App\Identity\Domain\ValueObject\HashedPassword;
use App\Identity\Domain\Repository\UserRepositoryInterface;
use App\Identity\Domain\Event\UserRegistered;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

#[AsMessageHandler]
final class RegisterUserHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly PasswordHasherFactoryInterface $passwordHasherFactory,
        private readonly MessageBusInterface $eventBus
    ) {}
    
    public function __invoke(RegisterUser $command): UserDTO
    {
        $email = new Email($command->email);
        
        // Check if email already exists
        if ($this->userRepository->emailExists($email)) {
            throw new \DomainException('Email already registered');
        }
        
        $userId = UserId::generate();
        $plainPassword = HashedPassword::fromPlainPassword($command->password);
        
        // Hash password
        $hasher = $this->passwordHasherFactory->getPasswordHasher(User::class);
        $hashedPasswordString = $hasher->hash($plainPassword->getHash());
        $hashedPassword = HashedPassword::fromHash($hashedPasswordString);
        
        $user = new User(
            $userId,
            $email,
            $hashedPassword,
            $command->firstName,
            $command->lastName
        );
        
        $this->userRepository->save($user);
        
        // Dispatch domain event
        $this->eventBus->dispatch(
            new UserRegistered(
                $userId,
                $email,
                $command->firstName,
                $command->lastName,
                new \DateTimeImmutable()
            )
        );
        
        return new UserDTO(
            id: $userId->getValue(),
            email: $email->getValue(),
            firstName: $user->getFirstName(),
            lastName: $user->getLastName(),
            status: $user->getStatus()->value,
            mfaEnabled: $user->isMfaEnabled(),
            emailVerified: $user->isEmailVerified(),
            emailVerifiedAt: $user->getEmailVerifiedAt(),
            createdAt: $user->getCreatedAt()
        );
    }
}
