<?php

declare(strict_types=1);

namespace App\Alert\Infrastructure\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Service de notification Telegram (Bot API)
 */
final class TelegramNotificationService
{
    private const API_URL = 'https://api.telegram.org/bot%s/sendMessage';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $botToken
    ) {
    }

    public function send(
        string $recipient,
        string $subject,
        string $message,
        array $metadata = []
    ): void {
        // $recipient est le chat_id Telegram
        $formattedMessage = $this->formatMessage($subject, $message, $metadata);

        $url = sprintf(self::API_URL, $this->botToken);

        $this->httpClient->request('POST', $url, [
            'json' => [
                'chat_id' => $recipient,
                'text' => $formattedMessage,
                'parse_mode' => 'Markdown',
                'disable_web_page_preview' => false
            ]
        ]);
    }

    private function formatMessage(string $subject, string $message, array $metadata): string
    {
        $formatted = sprintf("*%s*\n\n%s", $subject, $message);

        if (!empty($metadata['symbols'])) {
            $formatted .= sprintf("\n\n📊 *Affected:* %s", implode(', ', $metadata['symbols']));
        }

        if (isset($metadata['sentiment'])) {
            $emoji = match ($metadata['sentiment']) {
                'bullish' => '🚀',
                'bearish' => '📉',
                default => '📰'
            };
            $formatted .= sprintf("\n%s *Sentiment:* %s", $emoji, ucfirst($metadata['sentiment']));
        }

        if (isset($metadata['url'])) {
            $formatted .= sprintf("\n\n[Read more](%s)", $metadata['url']);
        }

        return substr($formatted, 0, 4000); // Limite Telegram
    }
}
