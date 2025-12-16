<?php

declare(strict_types=1);

namespace App\News\UI\Http\Controller;

use App\News\Application\Query\GetHighImportanceNews;
use App\News\Application\Query\GetNewsBySymbols;
use App\News\Application\Query\GetNewsFeed;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/news', name: 'news_')]
class NewsController extends AbstractController
{
    use HandleTrait;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    /**
     * Get news feed
     */
    #[Route('', name: 'feed', methods: ['GET'])]
    public function getFeed(Request $request): JsonResponse
    {
        $category = $request->query->get('category');
        $limit = min((int)$request->query->get('limit', 50), 100);

        try {
            $query = new GetNewsFeed($category, $limit);
            $articles = $this->handle($query);

            return new JsonResponse([
                'articles' => $articles,
                'total' => count($articles)
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to fetch news feed'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get news for specific symbols (watchlist)
     */
    #[Route('/symbols', name: 'by_symbols', methods: ['GET'])]
    public function getBySymbols(Request $request): JsonResponse
    {
        $symbolsParam = $request->query->get('symbols', '');
        $symbols = array_filter(array_map('trim', explode(',', $symbolsParam)));

        if (empty($symbols)) {
            return new JsonResponse([
                'error' => 'No symbols provided'
            ], Response::HTTP_BAD_REQUEST);
        }

        $limit = min((int)$request->query->get('limit', 20), 50);

        try {
            $query = new GetNewsBySymbols($symbols, $limit);
            $articles = $this->handle($query);

            return new JsonResponse([
                'articles' => $articles,
                'symbols' => $symbols,
                'total' => count($articles)
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to fetch news for symbols'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get high importance/impactful news (alerts)
     */
    #[Route('/important', name: 'important', methods: ['GET'])]
    public function getImportantNews(Request $request): JsonResponse
    {
        $minScore = (int)$request->query->get('minScore', 75);
        $limit = min((int)$request->query->get('limit', 20), 50);

        try {
            $query = new GetHighImportanceNews($minScore, $limit);
            $articles = $this->handle($query);

            return new JsonResponse([
                'articles' => $articles,
                'minScore' => $minScore,
                'total' => count($articles)
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to fetch important news'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
