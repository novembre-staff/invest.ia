<?php

declare(strict_types=1);

namespace App\News\Domain\Repository;

use App\News\Domain\Model\NewsArticle;
use App\News\Domain\ValueObject\NewsArticleId;
use App\News\Domain\ValueObject\NewsCategory;

interface NewsArticleRepositoryInterface
{
    public function save(NewsArticle $article): void;

    public function findById(NewsArticleId $id): ?NewsArticle;

    /**
     * Find recent news articles
     * 
     * @return NewsArticle[]
     */
    public function findRecent(int $limit = 50, ?NewsCategory $category = null): array;

    /**
     * Find news related to specific symbols
     * 
     * @param string[] $symbols
     * @return NewsArticle[]
     */
    public function findBySymbols(array $symbols, int $limit = 20): array;

    /**
     * Find high importance news
     * 
     * @return NewsArticle[]
     */
    public function findHighImportance(int $minScore = 75, int $limit = 20): array;

    /**
     * Check if article already exists by source URL
     */
    public function existsBySourceUrl(string $sourceUrl): bool;
}
