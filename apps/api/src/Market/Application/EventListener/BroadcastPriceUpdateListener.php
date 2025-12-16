<?php

declare(strict_types=1);

namespace App\Market\Application\EventListener;

use App\Market\Domain\Event\AssetPriceUpdated;
use App\Shared\Domain\Service\RealtimeServiceInterface;

/**
 * Envoie les mises à jour de prix en temps réel via WebSocket
 */
final class BroadcastPriceUpdateListener
{
    public function __construct(
        private readonly RealtimeServiceInterface $realtimeService
    ) {
    }

    public function __invoke(AssetPriceUpdated $event): void
    {
        // Broadcast à tous les utilisateurs suivant cet actif
        $this->realtimeService->sendToChannel(
            channel: sprintf('asset/%s', $event->symbol()),
            event: 'price.updated',
            data: [
                'symbol' => $event->symbol(),
                'price' => $event->price(),
                'change24h' => $event->change24h(),
                'volume24h' => $event->volume24h(),
                'timestamp' => $event->occurredAt()->format('c')
            ]
        );
    }
}
