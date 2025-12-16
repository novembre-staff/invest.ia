<?php

declare(strict_types=1);

namespace App\News\Domain\Model;

use App\News\Domain\ValueObject\ImportanceScore;
use App\News\Domain\ValueObject\NewsArticleId;
use App\News\Domain\ValueObject\NewsCategory;

/**
 * NewsArticle Aggregate Root
 * Represents a news article with importance scoring
 */
class NewsArticle
{
    private \DateTimeImmutable $createdAt;

    /**
     * @param string[] $relatedSymbols
     */
    public function __construct(
        private readonly NewsArticleId $id,
        private string $title,
        private string $summary,
        private ?string $content,
        private string $sourceUrl,
        private string $sourceName,
        private NewsCategory $category,
        private array $relatedSymbols,
        private ImportanceScore $importanceScore,
        private \DateTimeImmutable $publishedAt,
        private ?string $imageUrl = null
    ) {
        if (empty($title)) {
            throw new \InvalidArgumentException('Title cannot be empty');
        }

        if (empty($summary)) {
            throw new \InvalidArgumentException('Summary cannot be empty');
        }

        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): NewsArticleId
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getSummary(): string
    {
        return $this->summary;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function getSourceUrl(): string
    {
        return $this->sourceUrl;
    }

    public function getSourceName(): string
    {
        return $this->sourceName;
    }

    public function getCategory(): NewsCategory
    {
        return $this->category;
    }

    /**
     * @return string[]
     */
    public function getRelatedSymbols(): array
    {
        return $this->relatedSymbols;
    }

    public function getImportanceScore(): ImportanceScore
    {
        return $this->importanceScore;
    }

    public function getPublishedAt(): \DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Update importance score (e.g., after recalculation)
     */
    public function updateImportanceScore(ImportanceScore $score): void
    {
        $this->importanceScore = $score;
    }

    /**
     * Add a related symbol
     */
    public function addRelatedSymbol(string $symbol): void
    {
        if (!in_array($symbol, $this->relatedSymbols, true)) {
            $this->relatedSymbols[] = $symbol;
        }
    }

    /**
     * Check if this article is related to a specific symbol
     */
    public function isRelatedTo(string $symbol): bool
    {
        return in_array($symbol, $this->relatedSymbols, true);
    }

    /**
     * Check if this article should trigger an alert
     */
    public function shouldAlert(): bool
    {
        return $this->importanceScore->shouldAlert();
    }

    /**
     * Check if article is recent (less than 24h old)
     */
    public function isRecent(): bool
    {
        $now = new \DateTimeImmutable();
        $diff = $now->getTimestamp() - $this->publishedAt->getTimestamp();
        
        return $diff < 86400; // 24 hours
    }
}
