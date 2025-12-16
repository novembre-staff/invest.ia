<?php

declare(strict_types=1);

namespace App\News\Infrastructure\Service;

use App\News\Domain\Service\SentimentAnalyzerInterface;
use App\News\Domain\ValueObject\SentimentScore;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Analyseur de sentiment utilisant l'API OpenAI
 * Nécessite une clé API configurée via OPENAI_API_KEY
 */
final class OpenAISentimentAnalyzer implements SentimentAnalyzerInterface
{
    private const API_URL = 'https://api.openai.com/v1/chat/completions';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $apiKey,
        private readonly string $model = 'gpt-3.5-turbo'
    ) {
    }

    public function analyze(string $text): SentimentScore
    {
        $prompt = $this->buildPrompt($text);

        try {
            $response = $this->httpClient->request('POST', self::API_URL, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => $this->model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are a financial sentiment analyzer. Return only a JSON with a "score" field between -1.0 (very negative) and 1.0 (very positive).'
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'temperature' => 0.3,
                    'max_tokens' => 50
                ]
            ]);

            $data = $response->toArray();
            $content = $data['choices'][0]['message']['content'] ?? '';
            
            // Parse le JSON retourné
            $result = json_decode($content, true);
            $score = (float) ($result['score'] ?? 0.0);

            return SentimentScore::fromScore($score);
        } catch (\Exception $e) {
            // Fallback: retourne sentiment neutre en cas d'erreur
            return SentimentScore::fromScore(0.0);
        }
    }

    public function analyzeBatch(array $texts): array
    {
        // Pour l'instant, analyse séquentielle
        // TODO: Optimiser avec batch API d'OpenAI
        return array_map(
            fn(string $text) => $this->analyze($text),
            $texts
        );
    }

    private function buildPrompt(string $text): string
    {
        return sprintf(
            'Analyze the financial sentiment of this news:\n\n"%s"\n\nReturn JSON: {"score": <number between -1.0 and 1.0>}',
            substr($text, 0, 1000) // Limite à 1000 caractères
        );
    }
}
