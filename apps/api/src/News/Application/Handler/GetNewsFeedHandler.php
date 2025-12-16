<?php

declare(strict_types=1);

namespace App\News\Application\Handler;

use App\News\Application\DTO\NewsArticleDTO;
use App\News\Application\Query\GetNewsFeed;
use App\News\Domain\Event\HighImportanceNewsDetected;
use App\News\Domain\Repository\NewsArticleRepositoryInterface;
use App\News\Infrastructure\Adapter\NewsProviderInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class GetNewsFeedHandler
{
    public function __construct(
        private NewsArticleRepositoryInterface $newsRepository,
        private NewsProviderInterface $newsProvider,
        private MessageBusInterface $eventBus
    ) {
    }

    /**
     * @return NewsArticleDTO[]
     */
    public function __invoke(GetNewsFeed $query): array
    {
        // Fetch from external provider
        $articles = $this->newsProvider->fetchLatestNews($query->limit, $query->category);

        // Save new articles and dispatch events for high importance ones
        foreach ($articles as $article) {
            // Check if already exists
            if (!$this->newsRepository->existsBySourceUrl($article->getSourceUrl())) {
                $this->newsRepository->save($article);

                // Dispatch event if high importance
                if ($article->shouldAlert()) {
                    $this->eventBus->dispatch(
                        HighImportanceNewsDetected::now(
                            $article->getId(),
                            $article->getTitle(),
                            $article->getImportanceScore()->getValue(),
                            $article->getRelatedSymbols()
                        )
                    );
                }
            }
        }

        // Map to DTOs
        return array_map(
            fn($article) => NewsArticleDTO::fromDomain($article),
            $articles
        );
    }
}
