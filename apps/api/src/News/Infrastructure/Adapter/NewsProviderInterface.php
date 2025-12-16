<?php

declare(strict_types=1);

namespace App\News\Infrastructure\Adapter;

use App\News\Domain\Model\NewsArticle;

/**
 * Interface for fetching news from external sources
 */
interface NewsProviderInterface
{
    /**
     * Fetch latest news articles
     * 
     * @return NewsArticle[]
     */
    public function fetchLatestNews(int $limit = 50, ?string $category = null): array;

    /**
     * Fetch news related to specific crypto symbols
     * 
     * @param string[] $symbols
     * @return NewsArticle[]
     */
    public function fetchNewsBySymbols(array $symbols, int $limit = 20): array;

    /**
     * Search news by query
     * 
     * @return NewsArticle[]
     */
    public function searchNews(string $query, int $limit = 20): array;
}
