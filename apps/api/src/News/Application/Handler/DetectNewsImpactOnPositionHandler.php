<?php

declare(strict_types=1);

namespace App\News\Application\Handler;

use App\News\Application\Command\DetectNewsImpactOnPosition;
use App\News\Domain\Event\ImpactfulNewsDetected;
use App\News\Domain\Repository\NewsArticleRepositoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class DetectNewsImpactOnPositionHandler
{
    public function __construct(
        private NewsArticleRepositoryInterface $newsRepository,
        private MessageBusInterface $eventBus,
        private LoggerInterface $logger
    ) {
    }

    public function __invoke(DetectNewsImpactOnPosition $command): array
    {
        $cutoffDate = (new \DateTimeImmutable())->modify("-{$command->hoursBack} hours");
        
        // Find recent news mentioning this symbol
        $recentNews = $this->newsRepository->findImportantNews(
            symbols: [$command->symbol],
            since: $cutoffDate,
            limit: 20
        );

        $impactfulNews = [];

        foreach ($recentNews as $article) {
            if ($this->hasSignificantImpact($article, $command->symbol)) {
                $impactfulNews[] = [
                    'article_id' => $article->getId()->toString(),
                    'title' => $article->getTitle(),
                    'sentiment' => $article->getSentimentScore()?->getScore(),
                    'importance' => $article->getImportance()?->value,
                    'published_at' => $article->getPublishedAt()->format(\DateTimeInterface::ATOM)
                ];

                $this->logger->info('Impactful news detected for position', [
                    'position_id' => $command->positionId,
                    'symbol' => $command->symbol,
                    'article_id' => $article->getId()->toString(),
                    'sentiment' => $article->getSentimentScore()?->getScore()
                ]);

                // Dispatch event
                $event = ImpactfulNewsDetected::now(
                    positionId: $command->positionId,
                    symbol: $command->symbol,
                    articleId: $article->getId()->toString(),
                    sentiment: $article->getSentimentScore()?->getScore() ?? 0.0,
                    importance: $article->getImportance()?->value ?? 'medium'
                );

                $this->eventBus->dispatch($event);
            }
        }

        return $impactfulNews;
    }

    private function hasSignificantImpact($article, string $symbol): bool
    {
        // Check if article mentions the symbol
        if (!in_array($symbol, $article->getMentionedSymbols())) {
            return false;
        }

        // Check if sentiment is extreme or importance is high
        $sentiment = $article->getSentimentScore();
        $importance = $article->getImportance();

        return ($sentiment && abs($sentiment->getScore()) > 0.5) ||
               ($importance && in_array($importance->value, ['high', 'critical']));
    }
}
