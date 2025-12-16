<?php

declare(strict_types=1);

namespace App\Alert\Infrastructure\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Service de notification Discord (Webhook)
 */
final class DiscordNotificationService
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $webhookUrl
    ) {
    }

    public function send(
        string $recipient,
        string $subject,
        string $message,
        array $metadata = []
    ): void {
        $embed = [
            'title' => $subject,
            'description' => substr($message, 0, 2000), // Limite Discord
            'color' => $this->getSentimentColor($metadata['sentiment'] ?? null),
            'timestamp' => date('c')
        ];

        if (isset($metadata['url'])) {
            $embed['url'] = $metadata['url'];
        }

        if (!empty($metadata['symbols'])) {
            $embed['fields'][] = [
                'name' => 'Affected Assets',
                'value' => implode(', ', $metadata['symbols']),
                'inline' => true
            ];
        }

        if (isset($metadata['sentiment'])) {
            $embed['fields'][] = [
                'name' => 'Sentiment',
                'value' => ucfirst($metadata['sentiment']),
                'inline' => true
            ];
        }

        $payload = [
            'username' => 'invest.ia News Bot',
            'embeds' => [$embed]
        ];

        $this->httpClient->request('POST', $this->webhookUrl, [
            'json' => $payload
        ]);
    }

    private function getSentimentColor(?string $sentiment): int
    {
        return match ($sentiment) {
            'bullish' => 0x28a745, // Vert
            'bearish' => 0xdc3545, // Rouge
            default => 0x007bff   // Bleu
        };
    }
}
