<?php

declare(strict_types=1);

namespace App\Alert\Infrastructure\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Service de notification Push (Firebase Cloud Messaging)
 */
final class PushNotificationService
{
    private const FCM_API_URL = 'https://fcm.googleapis.com/fcm/send';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $serverKey
    ) {
    }

    public function send(
        string $recipient,
        string $subject,
        string $message,
        array $metadata = []
    ): void {
        // $recipient est le device token ou topic
        $payload = [
            'to' => $recipient,
            'notification' => [
                'title' => $subject,
                'body' => substr($message, 0, 200), // Limite FCM
                'icon' => 'ic_notification',
                'sound' => 'default'
            ],
            'data' => $metadata
        ];

        $this->httpClient->request('POST', self::FCM_API_URL, [
            'headers' => [
                'Authorization' => 'key=' . $this->serverKey,
                'Content-Type' => 'application/json',
            ],
            'json' => $payload
        ]);
    }
}
