<?php

declare(strict_types=1);

namespace App\News\Application\Command;

use App\News\Domain\Event\ImportantNewsDetected;
use App\News\Domain\Event\NewsAnalyzed;
use App\News\Domain\Model\NewsArticle;
use App\News\Domain\Repository\NewsArticleRepositoryInterface;
use App\News\Domain\Service\SentimentAnalyzerInterface;
use App\News\Domain\ValueObject\NewsImportance;
use App\Market\Domain\Repository\WatchlistRepositoryInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class AnalyzeNewsSentimentHandler
{
    public function __construct(
        private readonly NewsArticleRepositoryInterface $newsRepository,
        private readonly WatchlistRepositoryInterface $watchlistRepository,
        private readonly SentimentAnalyzerInterface $sentimentAnalyzer,
        private readonly MessageBusInterface $eventBus
    ) {
    }

    public function __invoke(AnalyzeNewsSentiment $command): void
    {
        // Récupère l'article
        $newsArticle = $this->newsRepository->findById($command->newsId());
        
        if (!$newsArticle) {
            throw new \RuntimeException(
                sprintf('News article %s not found', $command->newsId())
            );
        }

        // Analyse le sentiment
        $text = $this->buildAnalysisText($newsArticle);
        $sentiment = $this->sentimentAnalyzer->analyze($text);

        // Détecte les symboles affectés
        $affectedSymbols = $this->detectAffectedSymbols($newsArticle);
        
        // Vérifie si l'article mentionne des actifs suivis par des utilisateurs
        $mentionsWatchedAssets = $this->mentionsWatchedAssets($affectedSymbols);

        // Calcule l'importance
        $importance = NewsImportance::calculate(
            sentiment: $sentiment,
            sourceReliability: $newsArticle->source()->reliability(),
            mentionsWatchedAssets: $mentionsWatchedAssets,
            hasMarketImpact: $this->hasMarketImpact($newsArticle, $sentiment)
        );

        // Met à jour l'article avec le sentiment et l'importance
        $newsArticle->analyzeSentiment($sentiment, $importance);
        $this->newsRepository->save($newsArticle);

        // Dispatch événement d'analyse
        $this->eventBus->dispatch(
            new NewsAnalyzed(
                newsId: $newsArticle->id()->value(),
                sentimentScore: $sentiment->score(),
                sentimentLabel: $sentiment->label(),
                importance: $importance->value(),
                affectedSymbols: $affectedSymbols,
                occurredAt: new \DateTimeImmutable()
            )
        );

        // Si l'actualité est importante, dispatch un événement spécial
        if ($importance->shouldAlert()) {
            $this->eventBus->dispatch(
                new ImportantNewsDetected(
                    newsId: $newsArticle->id()->value(),
                    title: $newsArticle->title(),
                    importance: $importance->value(),
                    sentimentScore: $sentiment->score(),
                    sentimentLabel: $sentiment->label(),
                    affectedSymbols: $affectedSymbols,
                    occurredAt: new \DateTimeImmutable()
                )
            );
        }
    }

    private function buildAnalysisText(NewsArticle $article): string
    {
        return sprintf(
            '%s. %s. %s',
            $article->title(),
            $article->summary(),
            $article->content() ?? ''
        );
    }

    private function detectAffectedSymbols(NewsArticle $article): array
    {
        // Liste de symboles courants à rechercher dans le texte
        $commonSymbols = [
            'BTC', 'ETH', 'BNB', 'XRP', 'ADA', 'SOL', 'DOT', 'DOGE',
            'MATIC', 'LINK', 'UNI', 'AVAX', 'ATOM', 'LTC', 'BCH'
        ];

        $text = strtoupper($this->buildAnalysisText($article));
        $found = [];

        foreach ($commonSymbols as $symbol) {
            // Recherche le symbole avec des limites de mots
            if (preg_match('/\b' . preg_quote($symbol, '/') . '\b/', $text)) {
                $found[] = $symbol;
            }
        }

        return $found;
    }

    private function mentionsWatchedAssets(array $symbols): bool
    {
        if (empty($symbols)) {
            return false;
        }

        // Vérifie si au moins un symbole est dans une watchlist active
        foreach ($symbols as $symbol) {
            $watchlists = $this->watchlistRepository->findBySymbol($symbol);
            if (!empty($watchlists)) {
                return true;
            }
        }

        return false;
    }

    private function hasMarketImpact(NewsArticle $article, $sentiment): bool
    {
        // Mots-clés indiquant un impact marché significatif
        $impactKeywords = [
            'regulation', 'sec', 'banned', 'approved', 'etf', 'institutional',
            'adoption', 'partnership', 'listing', 'delisting', 'hack', 'exploit',
            'fork', 'upgrade', 'mainnet', 'lawsuit', 'settlement'
        ];

        $text = strtolower($this->buildAnalysisText($article));

        foreach ($impactKeywords as $keyword) {
            if (str_contains($text, $keyword)) {
                return true;
            }
        }

        // Sentiment extrême = impact probable
        return $sentiment->isExtreme();
    }
}
