<?php

declare(strict_types=1);

namespace App\News\Application\EventListener;

use App\News\Domain\Event\ImportantNewsDetected;
use App\Shared\Domain\Service\RealtimeServiceInterface;

/**
 * Envoie les actualités importantes en temps réel via WebSocket
 */
final class BroadcastImportantNewsListener
{
    public function __construct(
        private readonly RealtimeServiceInterface $realtimeService
    ) {
    }

    public function __invoke(ImportantNewsDetected $event): void
    {
        // Broadcast à tous les utilisateurs
        $this->realtimeService->broadcast(
            event: 'news.important',
            data: [
                'news_id' => $event->newsId(),
                'title' => $event->title(),
                'importance' => $event->importance(),
                'sentiment' => [
                    'label' => $event->sentimentLabel(),
                    'score' => $event->sentimentScore()
                ],
                'symbols' => $event->affectedSymbols(),
                'timestamp' => $event->occurredAt()->format('c')
            ]
        );

        // Envoie aussi par canal spécifique pour chaque symbole
        foreach ($event->affectedSymbols() as $symbol) {
            $this->realtimeService->sendToChannel(
                channel: sprintf('symbol/%s/news', $symbol),
                event: 'news.important',
                data: [
                    'news_id' => $event->newsId(),
                    'title' => $event->title(),
                    'importance' => $event->importance(),
                    'sentiment_label' => $event->sentimentLabel()
                ]
            );
        }
    }
}
