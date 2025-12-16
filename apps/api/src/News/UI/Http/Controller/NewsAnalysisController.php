<?php

declare(strict_types=1);

namespace App\News\UI\Http\Controller;

use App\News\Application\Command\AnalyzeNewsSentiment;
use App\News\Domain\Repository\NewsArticleRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/news', name: 'api_news_')]
final class NewsAnalysisController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly NewsArticleRepositoryInterface $newsRepository
    ) {
    }

    /**
     * Déclenche l'analyse de sentiment pour un article
     * 
     * POST /api/news/{id}/analyze
     */
    #[Route('/{id}/analyze', name: 'analyze_sentiment', methods: ['POST'])]
    public function analyzeSentiment(string $id): JsonResponse
    {
        try {
            $this->commandBus->dispatch(
                new AnalyzeNewsSentiment(articleId: $id)
            );

            return new JsonResponse([
                'success' => true,
                'message' => 'Sentiment analysis started',
                'news_id' => $id
            ], Response::HTTP_ACCEPTED);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Analyse en batch plusieurs articles
     * 
     * POST /api/news/analyze-batch
     * Body: {"news_ids": ["id1", "id2", "id3"]}
     */
    #[Route('/analyze-batch', name: 'analyze_batch', methods: ['POST'])]
    public function analyzeBatch(): JsonResponse
    {
        try {
            $data = json_decode(
                $this->getRequest()->getContent(),
                true
            );

            $newsIds = $data['news_ids'] ?? [];

            if (empty($newsIds)) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'No news IDs provided'
                ], Response::HTTP_BAD_REQUEST);
            }

            foreach ($newsIds as $newsId) {
                $this->commandBus->dispatch(
                    new AnalyzeNewsSentiment(articleId: $newsId)
                );
            }

            return new JsonResponse([
                'success' => true,
                'message' => sprintf('Analysis started for %d articles', count($newsIds)),
                'count' => count($newsIds)
            ], Response::HTTP_ACCEPTED);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Récupère les actualités importantes non lues
     * 
     * GET /api/news/important
     */
    #[Route('/important', name: 'get_important', methods: ['GET'])]
    public function getImportantNews(): JsonResponse
    {
        try {
            // TODO: Implémenter avec pagination
            $importantNews = $this->newsRepository->findImportantNews(limit: 20);

            $data = array_map(function ($article) {
                return [
                    'id' => $article->getId()->value(),
                    'title' => $article->getTitle(),
                    'summary' => $article->getSummary(),
                    'source' => $article->getSourceName(),
                    'url' => $article->getSourceUrl(),
                    'category' => $article->getCategory()->value(),
                    'symbols' => $article->getRelatedSymbols(),
                    'importance' => $article->getImportanceScore()->getValue(),
                    'sentiment' => [
                        'label' => $article->getSentiment()?->getValue(),
                        'score' => $article->getSentimentScore(),
                        'confidence' => $article->getSentimentConfidence()
                    ],
                    'published_at' => $article->getPublishedAt()->format('c'),
                    'is_high_impact' => $article->isHighImpact()
                ];
            }, $importantNews);

            return new JsonResponse([
                'success' => true,
                'data' => $data,
                'count' => count($data)
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function getRequest()
    {
        return $this->container->get('request_stack')->getCurrentRequest();
    }
}
