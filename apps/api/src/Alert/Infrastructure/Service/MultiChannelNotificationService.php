<?php

declare(strict_types=1);

namespace App\Alert\Infrastructure\Service;

use App\Alert\Domain\Service\NotificationServiceInterface;
use App\Alert\Domain\ValueObject\NotificationChannel;
use Psr\Log\LoggerInterface;

/**
 * Service de notification multi-canal
 * Délègue aux services spécifiques selon le canal
 */
final class MultiChannelNotificationService implements NotificationServiceInterface
{
    public function __construct(
        private readonly ?EmailNotificationService $emailService,
        private readonly ?PushNotificationService $pushService,
        private readonly ?SmsNotificationService $smsService,
        private readonly ?DiscordNotificationService $discordService,
        private readonly ?TelegramNotificationService $telegramService,
        private readonly LoggerInterface $logger
    ) {
    }

    public function send(
        NotificationChannel $channel,
        string $recipient,
        string $subject,
        string $message,
        array $metadata = []
    ): void {
        $service = $this->getServiceForChannel($channel);

        if (!$service) {
            $this->logger->warning(
                sprintf('Notification channel %s is not configured', $channel->value())
            );
            return;
        }

        try {
            $service->send($recipient, $subject, $message, $metadata);
            
            $this->logger->info(
                sprintf('Notification sent via %s to %s', $channel->value(), $recipient),
                ['channel' => $channel->value(), 'metadata' => $metadata]
            );
        } catch (\Exception $e) {
            $this->logger->error(
                sprintf('Failed to send notification via %s: %s', $channel->value(), $e->getMessage()),
                ['channel' => $channel->value(), 'recipient' => $recipient, 'exception' => $e]
            );
            
            throw $e;
        }
    }

    public function isChannelAvailable(NotificationChannel $channel): bool
    {
        return $this->getServiceForChannel($channel) !== null;
    }

    private function getServiceForChannel(NotificationChannel $channel): ?object
    {
        return match ($channel->value()) {
            NotificationChannel::EMAIL => $this->emailService,
            NotificationChannel::PUSH => $this->pushService,
            NotificationChannel::SMS => $this->smsService,
            NotificationChannel::DISCORD => $this->discordService,
            NotificationChannel::TELEGRAM => $this->telegramService,
            default => null
        };
    }
}
