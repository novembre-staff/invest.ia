<?php

declare(strict_types=1);

namespace App\Alert\Infrastructure\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Service de notification SMS (Twilio)
 */
final class SmsNotificationService
{
    private const TWILIO_API_URL = 'https://api.twilio.com/2010-04-01/Accounts/%s/Messages.json';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $accountSid,
        private readonly string $authToken,
        private readonly string $fromNumber
    ) {
    }

    public function send(
        string $recipient,
        string $subject,
        string $message,
        array $metadata = []
    ): void {
        // SMS limité à 160 caractères pour un segment
        $smsMessage = sprintf('%s: %s', $subject, $message);
        $smsMessage = substr($smsMessage, 0, 320); // 2 segments max

        $url = sprintf(self::TWILIO_API_URL, $this->accountSid);

        $this->httpClient->request('POST', $url, [
            'auth_basic' => [$this->accountSid, $this->authToken],
            'body' => [
                'From' => $this->fromNumber,
                'To' => $recipient,
                'Body' => $smsMessage
            ]
        ]);
    }
}
