<?php

declare(strict_types=1);

namespace App\Identity\Application\Handler;

use App\Identity\Application\Command\UpdateUserPreferences;
use App\Identity\Application\DTO\UserPreferencesDTO;
use App\Identity\Domain\Event\UserPreferencesUpdated;
use App\Identity\Domain\Repository\UserRepositoryInterface;
use App\Identity\Domain\ValueObject\UserId;
use App\Identity\Domain\ValueObject\UserPreferences;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class UpdateUserPreferencesHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly MessageBusInterface $eventBus
    ) {}

    public function __invoke(UpdateUserPreferences $command): UserPreferencesDTO
    {
        $userId = UserId::fromString($command->userId);
        $user = $this->userRepository->findById($userId);

        if (!$user) {
            throw new \DomainException('User not found.');
        }

        // Récupérer les préférences actuelles
        $currentPreferences = $user->getPreferences();
        
        // Construire les modifications (ne changer que ce qui est fourni)
        $changes = [];
        
        if ($command->reportingCurrency !== null) {
            $changes['reportingCurrency'] = $command->reportingCurrency;
        }
        
        if ($command->timezone !== null) {
            $changes['timezone'] = $command->timezone;
        }
        
        if ($command->language !== null) {
            $changes['language'] = $command->language;
        }
        
        if ($command->emailNotifications !== null) {
            $changes['emailNotifications'] = $command->emailNotifications;
        }
        
        if ($command->pushNotifications !== null) {
            $changes['pushNotifications'] = $command->pushNotifications;
        }
        
        if ($command->tradingAlerts !== null) {
            $changes['tradingAlerts'] = $command->tradingAlerts;
        }
        
        if ($command->newsAlerts !== null) {
            $changes['newsAlerts'] = $command->newsAlerts;
        }
        
        if ($command->theme !== null) {
            $changes['theme'] = $command->theme;
        }
        
        if ($command->soundEnabled !== null) {
            $changes['soundEnabled'] = $command->soundEnabled;
        }

        // Créer nouvelles préférences (immutable)
        $newPreferences = $currentPreferences->with($changes);

        // Mettre à jour l'utilisateur
        $user->updatePreferences($newPreferences);

        // Sauvegarder
        $this->userRepository->save($user);

        // Dispatch domain event
        $this->eventBus->dispatch(new UserPreferencesUpdated(
            $user->getId(),
            $user->getEmail(),
            $newPreferences,
            new \DateTimeImmutable()
        ));

        return UserPreferencesDTO::fromValueObject($newPreferences);
    }
}
