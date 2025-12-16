<?php

declare(strict_types=1);

namespace App\Identity\Application\Handler;

use App\Identity\Application\Command\LogoutUser;
use App\Identity\Domain\Event\UserLoggedOut;
use App\Identity\Domain\Repository\UserRepositoryInterface;
use App\Identity\Domain\ValueObject\UserId;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class LogoutUserHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly MessageBusInterface $eventBus
    ) {}

    public function __invoke(LogoutUser $command): void
    {
        $userId = UserId::fromString($command->userId);
        $user = $this->userRepository->findById($userId);

        if (!$user) {
            throw new \DomainException('User not found.');
        }

        // Dispatch domain event
        $this->eventBus->dispatch(new UserLoggedOut(
            $user->getId(),
            $user->getEmail(),
            new \DateTimeImmutable()
        ));

        // Note: La révocation du token JWT sera gérée par un listener
        // qui ajoutera le token à une blacklist Redis
    }
}
