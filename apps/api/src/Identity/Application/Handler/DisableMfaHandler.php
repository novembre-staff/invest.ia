<?php

declare(strict_types=1);

namespace App\Identity\Application\Handler;

use App\Identity\Application\Command\DisableMfa;
use App\Identity\Domain\Event\MfaDisabled;
use App\Identity\Domain\Repository\UserRepositoryInterface;
use App\Identity\Domain\ValueObject\UserId;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class DisableMfaHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly MessageBusInterface $eventBus
    ) {}

    public function __invoke(DisableMfa $command): void
    {
        $userId = UserId::fromString($command->userId);
        $user = $this->userRepository->findById($userId);

        if (!$user) {
            throw new \DomainException('User not found.');
        }

        if (!$user->isMfaEnabled()) {
            throw new \DomainException('MFA is not enabled for this user.');
        }

        // Désactiver MFA
        $user->disableMfa();

        // Sauvegarder
        $this->userRepository->save($user);

        // Dispatch domain event
        $this->eventBus->dispatch(new MfaDisabled(
            $user->getId(),
            $user->getEmail(),
            new \DateTimeImmutable()
        ));
    }
}
