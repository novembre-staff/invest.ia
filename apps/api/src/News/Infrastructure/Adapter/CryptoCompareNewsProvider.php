<?php

declare(strict_types=1);

namespace App\News\Infrastructure\Adapter;

use App\News\Domain\Model\NewsArticle;
use App\News\Domain\Service\ImportanceScorerInterface;
use App\News\Domain\ValueObject\ImportanceScore;
use App\News\Domain\ValueObject\NewsArticleId;
use App\News\Domain\ValueObject\NewsCategory;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * CryptoCompare News API adapter
 * Free tier: 50 requests/day, no API key required for basic usage
 * API Docs: https://min-api.cryptocompare.com/documentation
 */
final readonly class CryptoCompareNewsProvider implements NewsProviderInterface
{
    private const BASE_URL = 'https://min-api.cryptocompare.com/data/v2';

    public function __construct(
        private HttpClientInterface $httpClient,
        private ImportanceScorerInterface $importanceScorer
    ) {
    }

    public function fetchLatestNews(int $limit = 50, ?string $category = null): array
    {
        try {
            $params = [
                'lang' => 'EN',
            ];

            if ($category !== null) {
                $params['categories'] = $category;
            }

            $response = $this->httpClient->request('GET', self::BASE_URL . '/news/', [
                'query' => $params,
            ]);

            $data = $response->toArray();

            $articles = [];
            $newsData = $data['Data'] ?? [];
            
            foreach (array_slice($newsData, 0, $limit) as $item) {
                $article = $this->mapToNewsArticle($item);
                if ($article !== null) {
                    $articles[] = $article;
                }
            }

            return $articles;
        } catch (\Exception) {
            return [];
        }
    }

    public function fetchNewsBySymbols(array $symbols, int $limit = 20): array
    {
        // CryptoCompare doesn't have direct symbol filtering in free tier
        // Fetch recent news and filter by symbols in title/body
        $allNews = $this->fetchLatestNews(100);

        $filtered = [];
        foreach ($allNews as $article) {
            foreach ($symbols as $symbol) {
                if ($article->isRelatedTo($symbol)) {
                    $filtered[] = $article;
                    break;
                }
            }

            if (count($filtered) >= $limit) {
                break;
            }
        }

        return $filtered;
    }

    public function searchNews(string $query, int $limit = 20): array
    {
        // Fetch recent and filter by query
        $allNews = $this->fetchLatestNews(100);

        $queryLower = strtolower($query);
        $results = [];

        foreach ($allNews as $article) {
            $titleLower = strtolower($article->getTitle());
            $summaryLower = strtolower($article->getSummary());

            if (str_contains($titleLower, $queryLower) || str_contains($summaryLower, $queryLower)) {
                $results[] = $article;

                if (count($results) >= $limit) {
                    break;
                }
            }
        }

        return $results;
    }

    private function mapToNewsArticle(array $data): ?NewsArticle
    {
        try {
            $title = $data['title'] ?? '';
            $body = $data['body'] ?? '';
            $sourceUrl = $data['url'] ?? $data['guid'] ?? '';

            if (empty($title) || empty($sourceUrl)) {
                return null;
            }

            // Extract related symbols from categories and tags
            $relatedSymbols = $this->extractSymbols($data);

            // Determine category
            $category = $this->determineCategory($data);

            // Calculate importance score
            $publishedAt = new \DateTimeImmutable('@' . ($data['published_on'] ?? time()));
            $sourceName = $data['source_info']['name'] ?? $data['source'] ?? 'Unknown';

            $importanceScore = $this->importanceScorer->calculateScore(
                $title,
                $body,
                $sourceName,
                $relatedSymbols,
                $publishedAt
            );

            return new NewsArticle(
                id: NewsArticleId::generate(),
                title: $title,
                summary: $this->generateSummary($body),
                content: $body,
                sourceUrl: $sourceUrl,
                sourceName: $sourceName,
                category: $category,
                relatedSymbols: $relatedSymbols,
                importanceScore: $importanceScore,
                publishedAt: $publishedAt,
                imageUrl: $data['imageurl'] ?? null
            );
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * @return string[]
     */
    private function extractSymbols(array $data): array
    {
        $symbols = [];

        // From categories
        if (isset($data['categories']) && is_string($data['categories'])) {
            $categories = explode('|', $data['categories']);
            foreach ($categories as $cat) {
                $cat = trim($cat);
                if (preg_match('/^[A-Z]{2,10}$/', $cat)) {
                    $symbols[] = $cat;
                }
            }
        }

        // Common crypto symbols in title
        $title = strtoupper($data['title'] ?? '');
        $commonSymbols = ['BTC', 'ETH', 'USDT', 'BNB', 'XRP', 'ADA', 'DOGE', 'SOL', 'DOT', 'MATIC'];
        
        foreach ($commonSymbols as $symbol) {
            if (str_contains($title, $symbol) || str_contains($title, strtolower($symbol))) {
                $symbols[] = $symbol;
            }
        }

        return array_unique($symbols);
    }

    private function determineCategory(array $data): NewsCategory
    {
        $categories = strtolower($data['categories'] ?? '');

        if (str_contains($categories, 'regulation') || str_contains($categories, 'legal')) {
            return NewsCategory::REGULATION;
        }

        if (str_contains($categories, 'technology') || str_contains($categories, 'tech')) {
            return NewsCategory::TECHNOLOGY;
        }

        if (str_contains($categories, 'analysis') || str_contains($categories, 'market')) {
            return NewsCategory::MARKET_ANALYSIS;
        }

        return NewsCategory::CRYPTO;
    }

    private function generateSummary(string $body, int $maxLength = 200): string
    {
        $clean = strip_tags($body);
        $clean = preg_replace('/\s+/', ' ', $clean);
        $clean = trim($clean);

        if (strlen($clean) <= $maxLength) {
            return $clean;
        }

        $summary = substr($clean, 0, $maxLength);
        $lastSpace = strrpos($summary, ' ');
        
        if ($lastSpace !== false) {
            $summary = substr($summary, 0, $lastSpace);
        }

        return $summary . '...';
    }
}
