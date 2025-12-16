<?php

declare(strict_types=1);

namespace App\Alert\Application\Command;

use App\Alert\Domain\Service\NotificationServiceInterface;
use App\Alert\Domain\ValueObject\NotificationChannel;
use App\Identity\Domain\Repository\UserRepositoryInterface;
use App\News\Domain\Repository\NewsArticleRepositoryInterface;

final class SendNewsAlertHandler
{
    public function __construct(
        private readonly NewsArticleRepositoryInterface $newsRepository,
        private readonly UserRepositoryInterface $userRepository,
        private readonly NotificationServiceInterface $notificationService
    ) {
    }

    public function __invoke(SendNewsAlert $command): void
    {
        // Récupère l'article
        $newsArticle = $this->newsRepository->findById($command->newsId());
        
        if (!$newsArticle) {
            throw new \RuntimeException(
                sprintf('News article %s not found', $command->newsId())
            );
        }

        // Récupère l'utilisateur
        $user = $this->userRepository->findById($command->userId());
        
        if (!$user) {
            throw new \RuntimeException(
                sprintf('User %s not found', $command->userId())
            );
        }

        // Prépare le contenu de la notification
        $subject = $this->buildSubject($newsArticle);
        $message = $this->buildMessage($newsArticle);
        $metadata = $this->buildMetadata($newsArticle);

        // Envoie sur chaque canal demandé
        foreach ($command->channels() as $channelStr) {
            try {
                $channel = NotificationChannel::fromString($channelStr);
                
                if (!$this->notificationService->isChannelAvailable($channel)) {
                    continue;
                }

                $recipient = $this->getRecipientForChannel($user, $channel);
                
                if ($recipient) {
                    $this->notificationService->send(
                        channel: $channel,
                        recipient: $recipient,
                        subject: $subject,
                        message: $message,
                        metadata: $metadata
                    );
                }
            } catch (\InvalidArgumentException $e) {
                // Canal invalide, on ignore
                continue;
            }
        }
    }

    private function buildSubject($newsArticle): string
    {
        $sentiment = $newsArticle->getSentiment();
        $sentimentEmoji = match ($sentiment?->getValue()) {
            'bullish' => '🚀',
            'bearish' => '📉',
            default => '📰'
        };

        return sprintf(
            '%s Important News: %s',
            $sentimentEmoji,
            substr($newsArticle->getTitle(), 0, 50)
        );
    }

    private function buildMessage($newsArticle): string
    {
        $symbols = $newsArticle->getRelatedSymbols();
        $symbolsStr = !empty($symbols) ? implode(', ', $symbols) : 'General market';

        return sprintf(
            "%s\n\n%s\n\nAffected assets: %s\nSentiment: %s (score: %.2f)\nImportance: %.1f/10\n\nRead more: %s",
            $newsArticle->getTitle(),
            $newsArticle->getSummary(),
            $symbolsStr,
            $newsArticle->getSentiment()?->getValue() ?? 'unknown',
            $newsArticle->getSentimentScore() ?? 0.0,
            $newsArticle->getImportanceScore()->getValue(),
            $newsArticle->getSourceUrl()
        );
    }

    private function buildMetadata($newsArticle): array
    {
        return [
            'news_id' => $newsArticle->getId()->value(),
            'category' => $newsArticle->getCategory()->value(),
            'symbols' => $newsArticle->getRelatedSymbols(),
            'importance' => $newsArticle->getImportanceScore()->getValue(),
            'sentiment' => $newsArticle->getSentiment()?->getValue(),
            'sentiment_score' => $newsArticle->getSentimentScore(),
            'url' => $newsArticle->getSourceUrl(),
            'published_at' => $newsArticle->getPublishedAt()->format('c')
        ];
    }

    private function getRecipientForChannel($user, NotificationChannel $channel): ?string
    {
        return match ($channel->value()) {
            NotificationChannel::EMAIL => $user->email()->value(),
            NotificationChannel::SMS => $user->phoneNumber()?->value(),
            NotificationChannel::PUSH => $user->id()->value(), // User ID pour push notifications
            NotificationChannel::DISCORD => $user->discordId()?->value(),
            NotificationChannel::TELEGRAM => $user->telegramId()?->value(),
            default => null
        };
    }
}
