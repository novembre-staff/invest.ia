<?php

declare(strict_types=1);

namespace App\Alert\Domain\Service;

use App\Alert\Domain\ValueObject\NotificationChannel;

interface NotificationServiceInterface
{
    /**
     * Envoie une notification via un canal spécifique
     * 
     * @param string $recipient Destinataire (email, phone, user_id, etc.)
     * @param string $subject Sujet/titre de la notification
     * @param string $message Contenu de la notification
     * @param array<string, mixed> $metadata Métadonnées additionnelles
     */
    public function send(
        NotificationChannel $channel,
        string $recipient,
        string $subject,
        string $message,
        array $metadata = []
    ): void;

    /**
     * Vérifie si un canal est disponible/configuré
     */
    public function isChannelAvailable(NotificationChannel $channel): bool;
}
