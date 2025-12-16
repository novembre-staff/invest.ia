<?php

declare(strict_types=1);

namespace App\News\Infrastructure\Service;

use App\News\Domain\Service\SentimentAnalyzerInterface;
use App\News\Domain\ValueObject\NewsSentiment;

/**
 * Lexicon-based sentiment analyzer
 * Uses predefined word lists to determine sentiment
 * Fast and deterministic, good for financial news
 */
class LexiconSentimentAnalyzer implements SentimentAnalyzerInterface
{
    // Bullish/positive keywords for financial news
    private const POSITIVE_KEYWORDS = [
        'surge', 'rally', 'bullish', 'breakthrough', 'profit', 'gain', 'growth', 'soar',
        'record', 'high', 'rise', 'increase', 'boom', 'success', 'strong', 'positive',
        'optimistic', 'upgrade', 'beat', 'exceed', 'outperform', 'momentum', 'breakout',
        'adoption', 'partnership', 'acquisition', 'expansion', 'innovation', 'milestone',
        'approve', 'approval', 'launch', 'recover', 'rebound', 'victory', 'winning'
    ];

    // Bearish/negative keywords for financial news
    private const NEGATIVE_KEYWORDS = [
        'crash', 'plunge', 'bearish', 'decline', 'loss', 'drop', 'fall', 'slump',
        'low', 'decrease', 'collapse', 'fail', 'weak', 'negative', 'pessimistic',
        'downgrade', 'miss', 'underperform', 'crisis', 'concern', 'warning', 'risk',
        'volatility', 'uncertainty', 'threat', 'lawsuit', 'scandal', 'breach', 'hack',
        'ban', 'regulation', 'shutdown', 'delay', 'reject', 'investigate', 'fraud'
    ];

    // Very strong impact keywords
    private const VERY_POSITIVE_KEYWORDS = [
        'massive surge', 'all-time high', 'record-breaking', 'explosive growth',
        'game-changer', 'revolutionary', 'unprecedented', 'major breakthrough'
    ];

    private const VERY_NEGATIVE_KEYWORDS = [
        'massive crash', 'catastrophic', 'devastating', 'bankruptcy', 'collapse',
        'emergency', 'panic', 'disaster', 'major breach', 'critical failure'
    ];

    public function analyze(string $text): NewsSentiment
    {
        $result = $this->analyzeDetailed($text);
        return $result['sentiment'];
    }

    public function analyzeDetailed(string $text): array
    {
        $text = strtolower($text);
        
        // Count very strong keywords first
        $veryPositiveCount = $this->countKeywords($text, self::VERY_POSITIVE_KEYWORDS);
        $veryNegativeCount = $this->countKeywords($text, self::VERY_NEGATIVE_KEYWORDS);

        // Count regular keywords
        $positiveCount = $this->countKeywords($text, self::POSITIVE_KEYWORDS);
        $negativeCount = $this->countKeywords($text, self::NEGATIVE_KEYWORDS);

        // Weight very strong keywords more heavily
        $totalPositive = $positiveCount + ($veryPositiveCount * 3);
        $totalNegative = $negativeCount + ($veryNegativeCount * 3);

        // Calculate score (-1.0 to 1.0)
        $totalKeywords = $totalPositive + $totalNegative;
        
        if ($totalKeywords === 0) {
            return [
                'sentiment' => NewsSentiment::NEUTRAL,
                'score' => 0.0,
                'confidence' => 0.0
            ];
        }

        $score = ($totalPositive - $totalNegative) / max($totalKeywords, 1);
        
        // Confidence based on keyword density
        $wordCount = str_word_count($text);
        $keywordDensity = $totalKeywords / max($wordCount, 1);
        $confidence = min($keywordDensity * 10, 1.0); // Cap at 1.0

        $sentiment = NewsSentiment::fromScore($score);

        return [
            'sentiment' => $sentiment,
            'score' => $score,
            'confidence' => $confidence
        ];
    }

    /**
     * Count occurrences of keywords in text
     * 
     * @param string[] $keywords
     */
    private function countKeywords(string $text, array $keywords): int
    {
        $count = 0;
        
        foreach ($keywords as $keyword) {
            // Use word boundaries to avoid partial matches
            $pattern = '/\b' . preg_quote($keyword, '/') . '\b/';
            $count += preg_match_all($pattern, $text);
        }

        return $count;
    }
}
