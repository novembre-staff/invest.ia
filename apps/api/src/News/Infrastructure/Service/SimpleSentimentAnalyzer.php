<?php

declare(strict_types=1);

namespace App\News\Infrastructure\Service;

use App\News\Domain\Service\SentimentAnalyzerInterface;
use App\News\Domain\ValueObject\SentimentScore;

/**
 * Analyseur de sentiment simple basé sur des mots-clés
 * Pour production: remplacer par un service NLP (OpenAI, Hugging Face, etc.)
 */
final class SimpleSentimentAnalyzer implements SentimentAnalyzerInterface
{
    private const POSITIVE_WORDS = [
        'bullish', 'surge', 'rally', 'gain', 'profit', 'growth', 'up', 'rise',
        'increase', 'positive', 'success', 'strong', 'boom', 'soar', 'climb',
        'breakout', 'momentum', 'optimistic', 'upgrade', 'outperform', 'beat',
        'excellent', 'record', 'high', 'jump', 'advance', 'winning'
    ];

    private const NEGATIVE_WORDS = [
        'bearish', 'crash', 'fall', 'loss', 'decline', 'down', 'drop',
        'decrease', 'negative', 'fail', 'weak', 'collapse', 'plunge', 'tumble',
        'breakdown', 'sell-off', 'pessimistic', 'downgrade', 'underperform', 'miss',
        'poor', 'low', 'sink', 'slide', 'losing', 'crisis', 'warning', 'risk'
    ];

    private const AMPLIFIERS = [
        'very', 'extremely', 'significantly', 'massively', 'huge', 'major'
    ];

    public function analyze(string $text): SentimentScore
    {
        $text = strtolower($text);
        $words = preg_split('/\s+/', $text);

        $positiveCount = 0;
        $negativeCount = 0;
        $amplifier = 1.0;

        foreach ($words as $index => $word) {
            // Vérifie si le mot précédent est un amplificateur
            if ($index > 0 && in_array($words[$index - 1], self::AMPLIFIERS, true)) {
                $amplifier = 1.5;
            } else {
                $amplifier = 1.0;
            }

            if (in_array($word, self::POSITIVE_WORDS, true)) {
                $positiveCount += $amplifier;
            } elseif (in_array($word, self::NEGATIVE_WORDS, true)) {
                $negativeCount += $amplifier;
            }
        }

        $totalWords = count($words);
        $score = 0.0;

        if ($totalWords > 0) {
            $netSentiment = ($positiveCount - $negativeCount) / $totalWords;
            // Normalise entre -1 et 1 (approximation)
            $score = max(-1.0, min(1.0, $netSentiment * 10));
        }

        return SentimentScore::fromScore($score);
    }

    public function analyzeBatch(array $texts): array
    {
        return array_map(
            fn(string $text) => $this->analyze($text),
            $texts
        );
    }
}
