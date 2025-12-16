<?php

declare(strict_types=1);

namespace App\News\Application\Handler;

use App\News\Application\DTO\NewsArticleDTO;
use App\News\Application\Query\GetNewsBySymbols;
use App\News\Infrastructure\Adapter\NewsProviderInterface;

final readonly class GetNewsBySymbolsHandler
{
    public function __construct(
        private NewsProviderInterface $newsProvider
    ) {
    }

    /**
     * @return NewsArticleDTO[]
     */
    public function __invoke(GetNewsBySymbols $query): array
    {
        $articles = $this->newsProvider->fetchNewsBySymbols(
            $query->symbols,
            $query->limit
        );

        return array_map(
            fn($article) => NewsArticleDTO::fromDomain($article),
            $articles
        );
    }
}
