<?php

declare(strict_types=1);

namespace App\News\Domain\Service;

use App\News\Domain\ValueObject\SentimentScore;

interface SentimentAnalyzerInterface
{
    /**
     * Analyse le sentiment d'un texte et retourne un score entre -1 et 1
     * -1 = très négatif, 0 = neutre, +1 = très positif
     */
    public function analyze(string $text): SentimentScore;

    /**
     * Analyse en batch pour optimiser les performances
     * 
     * @param array<string> $texts
     * @return array<SentimentScore>
     */
    public function analyzeBatch(array $texts): array;
}
