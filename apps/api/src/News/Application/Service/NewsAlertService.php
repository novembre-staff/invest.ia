<?php

declare(strict_types=1);

namespace App\News\Application\Service;

use App\Identity\Domain\Model\User;
use App\News\Domain\Event\HighImportanceNewsDetected;
use Psr\Log\LoggerInterface;

/**
 * Service to handle high importance news alerts
 * Sends notifications to users when important news is detected
 */
final readonly class NewsAlertService
{
    public function __construct(
        private LoggerInterface $logger
    ) {
    }

    /**
     * Handle high importance news detection
     * In a full implementation, this would:
     * - Find users interested in the related symbols
     * - Check their notification preferences
     * - Send push notifications / emails
     * - Store alert history
     */
    public function handleHighImportanceNews(HighImportanceNewsDetected $event): void
    {
        $this->logger->info('High importance news detected', [
            'articleId' => $event->articleId->getValue(),
            'title' => $event->title,
            'importanceScore' => $event->importanceScore,
            'relatedSymbols' => $event->relatedSymbols,
            'occurredAt' => $event->occurredAt->format(\DateTimeInterface::ATOM)
        ]);

        // TODO: Implement notification logic
        // 1. Query users with watchlists containing any of the related symbols
        // 2. Filter users with newsAlerts preference enabled
        // 3. Send notifications via:
        //    - Push notifications (Firebase Cloud Messaging)
        //    - Email (Symfony Mailer)
        //    - WebSocket (Mercure for real-time)
        // 4. Store notification in user_news_alerts table for history
    }
}
