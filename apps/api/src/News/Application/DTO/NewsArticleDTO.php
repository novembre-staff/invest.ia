<?php

declare(strict_types=1);

namespace App\News\Application\DTO;

use App\News\Domain\Model\NewsArticle;

final readonly class NewsArticleDTO
{
    /**
     * @param string[] $relatedSymbols
     */
    private function __construct(
        public string $id,
        public string $title,
        public string $summary,
        public ?string $content,
        public string $sourceUrl,
        public string $sourceName,
        public string $category,
        public array $relatedSymbols,
        public int $importanceScore,
        public string $importanceLevel,
        public bool $shouldAlert,
        public string $publishedAt,
        public ?string $imageUrl
    ) {
    }

    public static function fromDomain(NewsArticle $article): self
    {
        return new self(
            id: $article->getId()->getValue(),
            title: $article->getTitle(),
            summary: $article->getSummary(),
            content: $article->getContent(),
            sourceUrl: $article->getSourceUrl(),
            sourceName: $article->getSourceName(),
            category: $article->getCategory()->value,
            relatedSymbols: $article->getRelatedSymbols(),
            importanceScore: $article->getImportanceScore()->getValue(),
            importanceLevel: $article->getImportanceScore()->getLevel(),
            shouldAlert: $article->shouldAlert(),
            publishedAt: $article->getPublishedAt()->format(\DateTimeInterface::ATOM),
            imageUrl: $article->getImageUrl()
        );
    }
}
