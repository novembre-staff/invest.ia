<?php

declare(strict_types=1);

namespace App\Alert\Application\EventListener;

use App\Alert\Application\Command\SendNewsAlert;
use App\News\Domain\Event\ImportantNewsDetected;
use App\Identity\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Écoute les événements ImportantNewsDetected et envoie des alertes aux utilisateurs concernés
 */
final class ImportantNewsAlertListener
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly MessageBusInterface $commandBus
    ) {
    }

    public function __invoke(ImportantNewsDetected $event): void
    {
        // Trouve tous les utilisateurs concernés par cette actualité
        $affectedUsers = $this->findAffectedUsers($event);

        // Pour chaque utilisateur, envoie une alerte selon ses préférences
        foreach ($affectedUsers as $user) {
            $channels = $this->getUserNotificationChannels($user);

            if (empty($channels)) {
                continue;
            }

            $this->commandBus->dispatch(
                new SendNewsAlert(
                    newsId: $event->newsId(),
                    userId: $user->id()->value(),
                    channels: $channels
                )
            );
        }
    }

    /**
     * Trouve les utilisateurs qui devraient être alertés pour cette actualité
     */
    private function findAffectedUsers(ImportantNewsDetected $event): array
    {
        $affectedSymbols = $event->affectedSymbols();

        if (empty($affectedSymbols)) {
            // Actualité générale : alerte tous les utilisateurs avec notifications activées
            return $this->userRepository->findWithNewsAlertsEnabled();
        }

        // Actualité ciblée : alerte les utilisateurs suivant ces actifs
        return $this->userRepository->findByWatchedSymbols($affectedSymbols);
    }

    /**
     * Retourne les canaux de notification préférés de l'utilisateur
     */
    private function getUserNotificationChannels($user): array
    {
        $preferences = $user->preferences();
        $channels = [];

        if ($preferences->newsAlertsEnabled()) {
            // Par défaut, email pour tous
            $channels[] = 'email';

            // Ajoute les autres canaux selon les préférences
            if ($preferences->pushNotificationsEnabled()) {
                $channels[] = 'push';
            }

            if ($preferences->smsAlertsEnabled()) {
                $channels[] = 'sms';
            }

            // Canaux tiers configurés
            if ($user->discordId()) {
                $channels[] = 'discord';
            }

            if ($user->telegramId()) {
                $channels[] = 'telegram';
            }
        }

        return $channels;
    }
}
