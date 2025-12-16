<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Service;

use App\Shared\Domain\Service\RealtimeServiceInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

/**
 * Service WebSocket utilisant Mercure pour les mises à jour temps réel
 * https://mercure.rocks/
 */
final class MercureRealtimeService implements RealtimeServiceInterface
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $mercureUrl,
        private readonly string $mercureJwtSecret,
        private readonly LoggerInterface $logger
    ) {
    }

    public function sendToUser(string $userId, string $event, array $data): void
    {
        $this->publish(
            topics: [sprintf('user/%s', $userId)],
            event: $event,
            data: $data
        );
    }

    public function broadcast(string $event, array $data): void
    {
        $this->publish(
            topics: ['broadcast'],
            event: $event,
            data: $data
        );
    }

    public function sendToChannel(string $channel, string $event, array $data): void
    {
        $this->publish(
            topics: [sprintf('channel/%s', $channel)],
            event: $event,
            data: $data
        );
    }

    private function publish(array $topics, string $event, array $data): void
    {
        try {
            $jwt = $this->generateJwt();

            $payload = [
                'event' => $event,
                'data' => $data,
                'timestamp' => time()
            ];

            foreach ($topics as $topic) {
                $this->httpClient->request('POST', $this->mercureUrl, [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $jwt,
                    ],
                    'body' => [
                        'topic' => $topic,
                        'data' => json_encode($payload),
                    ]
                ]);
            }

            $this->logger->debug('Realtime update sent', [
                'topics' => $topics,
                'event' => $event
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to send realtime update', [
                'topics' => $topics,
                'event' => $event,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function generateJwt(): string
    {
        // TODO: Implémenter génération JWT pour Mercure
        // https://mercure.rocks/docs/hub/authentication
        return $this->mercureJwtSecret;
    }
}
