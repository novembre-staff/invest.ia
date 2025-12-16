<?php

declare(strict_types=1);

namespace App\News\Application\Handler;

use App\News\Application\DTO\NewsArticleDTO;
use App\News\Application\Query\GetHighImportanceNews;
use App\News\Domain\Repository\NewsArticleRepositoryInterface;

final readonly class GetHighImportanceNewsHandler
{
    public function __construct(
        private NewsArticleRepositoryInterface $newsRepository
    ) {
    }

    /**
     * @return NewsArticleDTO[]
     */
    public function __invoke(GetHighImportanceNews $query): array
    {
        $articles = $this->newsRepository->findHighImportance(
            $query->minScore,
            $query->limit
        );

        return array_map(
            fn($article) => NewsArticleDTO::fromDomain($article),
            $articles
        );
    }
}
