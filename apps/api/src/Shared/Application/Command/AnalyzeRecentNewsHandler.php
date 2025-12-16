<?php

declare(strict_types=1);

namespace App\Shared\Application\Command;

use App\News\Application\Command\AnalyzeNewsSentiment;
use App\News\Domain\Repository\NewsArticleRepositoryInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Psr\Log\LoggerInterface;

/**
 * Handler pour analyser automatiquement les actualités récentes
 * Utilisé par les tâches planifiées (cron/scheduler)
 */
final class AnalyzeRecentNewsHandler
{
    public function __construct(
        private readonly NewsArticleRepositoryInterface $newsRepository,
        private readonly MessageBusInterface $commandBus,
        private readonly LoggerInterface $logger
    ) {
    }

    public function __invoke(AnalyzeRecentNews $command): array
    {
        $this->logger->info('Starting scheduled news analysis', [
            'maxArticles' => $command->maxArticles(),
            'hoursBack' => $command->hoursBack()
        ]);

        // Récupère les articles non analysés ou récents
        $articles = $this->newsRepository->findUnanalyzedRecent(
            maxResults: $command->maxArticles(),
            hoursBack: $command->hoursBack()
        );

        $analyzed = 0;
        $errors = 0;

        foreach ($articles as $article) {
            try {
                $this->commandBus->dispatch(
                    new AnalyzeNewsSentiment(articleId: $article->getId()->value())
                );
                $analyzed++;
            } catch (\Exception $e) {
                $errors++;
                $this->logger->error('Failed to dispatch analysis for article', [
                    'article_id' => $article->getId()->value(),
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->logger->info('Completed scheduled news analysis', [
            'total_found' => count($articles),
            'analyzed' => $analyzed,
            'errors' => $errors
        ]);

        return [
            'total_found' => count($articles),
            'dispatched' => $analyzed,
            'errors' => $errors
        ];
    }
}
