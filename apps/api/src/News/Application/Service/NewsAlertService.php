<?php

declare(strict_types=1);

namespace App\News\Application\Service;

use App\Identity\Domain\Model\User;
use App\News\Domain\Event\HighImportanceNewsDetected;
use Psr\Log\LoggerInterface;

use App\Market\Domain\Repository\WatchlistRepositoryInterface;
use App\Shared\Application\Service\NotificationServiceInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Service to handle high importance news alerts
 * Sends notifications to users when important news is detected
 */
final readonly class NewsAlertService
{
    public function __construct(
        private LoggerInterface $logger,
        private WatchlistRepositoryInterface $watchlistRepository,
        private NotificationServiceInterface $notificationService,
        private MessageBusInterface $eventBus
    ) {
    }

    /**
     * Handle high importance news detection
     * Finds interested users and sends multi-channel notifications
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

        // Find users interested in the related symbols
        $interestedUsers = $this->findInterestedUsers($event->relatedSymbols);

        if (empty($interestedUsers)) {
            $this->logger->debug('No interested users found for news alert');
            return;
        }

        // Send notifications to each interested user
        foreach ($interestedUsers as $userId) {
            $this->sendNewsAlert($userId, $event);
        }

        $this->logger->info('News alerts sent', [
            'articleId' => $event->articleId->getValue(),
            'usersNotified' => count($interestedUsers)
        ]);
    }

    /**
     * Find users with watchlists containing any of the symbols
     * 
     * @param string[] $symbols
     * @return string[] Array of user IDs
     */
    private function findInterestedUsers(array $symbols): array
    {
        if (empty($symbols)) {
            return [];
        }

        $userIds = [];
        
        // Query watchlists for each symbol
        foreach ($symbols as $symbol) {
            $watchlists = $this->watchlistRepository->findBySymbol($symbol);
            
            foreach ($watchlists as $watchlist) {
                $userId = $watchlist->getUserId()->getValue();
                $userIds[$userId] = true; // Use array key to deduplicate
            }
        }

        return array_keys($userIds);
    }

    /**
     * Send notification to a specific user
     */
    private function sendNewsAlert(string $userId, HighImportanceNewsDetected $event): void
    {
        try {
            $this->notificationService->sendNotification(
                userId: $userId,
                title: '📰 Important News Alert',
                message: $event->title,
                type: 'news_alert',
                data: [
                    'articleId' => $event->articleId->getValue(),
                    'importanceScore' => $event->importanceScore,
                    'relatedSymbols' => $event->relatedSymbols,
                    'category' => $event->category
                ],
                channels: ['push', 'email', 'in_app']
            );
        } catch (\Exception $e) {
            $this->logger->error('Failed to send news alert', [
                'userId' => $userId,
                'articleId' => $event->articleId->getValue(),
                'error' => $e->getMessage()
            ]);
        }
    }
}
