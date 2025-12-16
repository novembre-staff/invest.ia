<?php

declare(strict_types=1);

namespace App\Shared\Domain\Service;

/**
 * Interface pour envoyer des mises à jour temps réel via WebSocket
 */
interface RealtimeServiceInterface
{
    /**
     * Envoie une mise à jour à un utilisateur spécifique
     */
    public function sendToUser(string $userId, string $event, array $data): void;

    /**
     * Envoie une mise à jour à tous les utilisateurs
     */
    public function broadcast(string $event, array $data): void;

    /**
     * Envoie une mise à jour à un channel/room spécifique
     */
    public function sendToChannel(string $channel, string $event, array $data): void;
}
