<?php

declare(strict_types=1);

namespace App\Identity\Application\Handler;

use App\Identity\Application\Command\VerifyUserEmail;
use App\Identity\Domain\ValueObject\UserId;
use App\Identity\Domain\Repository\UserRepositoryInterface;
use App\Identity\Domain\Event\UserEmailVerified;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class VerifyUserEmailHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly MessageBusInterface $eventBus
    ) {}
    
    public function __invoke(VerifyUserEmail $command): void
    {
        $userId = UserId::fromString($command->userId);
        
        $user = $this->userRepository->findById($userId);
        
        if (!$user) {
            throw new \DomainException('User not found');
        }
        
        // TODO: Validate token (implement token service)
        // For now, we just verify the email
        
        $user->verifyEmail();
        
        $this->userRepository->save($user);
        
        // Dispatch domain event
        $this->eventBus->dispatch(
            new UserEmailVerified($userId, new \DateTimeImmutable())
        );
    }
}
