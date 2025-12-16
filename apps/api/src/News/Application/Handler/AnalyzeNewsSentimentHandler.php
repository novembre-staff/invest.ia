<?php

declare(strict_types=1);

namespace App\News\Application\Handler;

use App\News\Application\Command\AnalyzeNewsSentiment;
use App\News\Domain\Repository\NewsArticleRepositoryInterface;
use App\News\Domain\Service\SentimentAnalyzerInterface;
use App\News\Domain\ValueObject\NewsArticleId;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class AnalyzeNewsSentimentHandler
{
    public function __construct(
        private readonly NewsArticleRepositoryInterface $newsRepository,
        private readonly SentimentAnalyzerInterface $sentimentAnalyzer
    ) {}

    public function __invoke(AnalyzeNewsSentiment $command): void
    {
        $articleId = NewsArticleId::fromString($command->articleId);
        $article = $this->newsRepository->findById($articleId);

        if (!$article) {
            throw new \DomainException('News article not found.');
        }

        // Combine title, summary and content for analysis
        $textToAnalyze = sprintf(
            '%s. %s. %s',
            $article->getTitle(),
            $article->getSummary(),
            $article->getContent() ?? ''
        );

        // Perform sentiment analysis
        $result = $this->sentimentAnalyzer->analyzeDetailed($textToAnalyze);

        // Update article with sentiment
        $article->updateSentiment(
            sentiment: $result['sentiment'],
            score: $result['score'],
            confidence: $result['confidence']
        );

        $this->newsRepository->save($article);
    }
}
