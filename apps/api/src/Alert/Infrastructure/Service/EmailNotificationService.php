<?php

declare(strict_types=1);

namespace App\Alert\Infrastructure\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

final class EmailNotificationService
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly string $fromEmail,
        private readonly string $fromName = 'invest.ia'
    ) {
    }

    public function send(
        string $recipient,
        string $subject,
        string $message,
        array $metadata = []
    ): void {
        $htmlMessage = $this->buildHtmlMessage($subject, $message, $metadata);

        $email = (new Email())
            ->from(sprintf('%s <%s>', $this->fromName, $this->fromEmail))
            ->to($recipient)
            ->subject($subject)
            ->text($message)
            ->html($htmlMessage);

        $this->mailer->send($email);
    }

    private function buildHtmlMessage(string $subject, string $message, array $metadata): string
    {
        $htmlMessage = sprintf('<h2>%s</h2>', htmlspecialchars($subject));
        $htmlMessage .= sprintf('<p>%s</p>', nl2br(htmlspecialchars($message)));

        if (isset($metadata['url'])) {
            $htmlMessage .= sprintf(
                '<p><a href="%s" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">Read Full Article</a></p>',
                htmlspecialchars($metadata['url'])
            );
        }

        if (!empty($metadata['symbols'])) {
            $htmlMessage .= '<p><strong>Affected assets:</strong> ';
            $htmlMessage .= htmlspecialchars(implode(', ', $metadata['symbols']));
            $htmlMessage .= '</p>';
        }

        if (isset($metadata['sentiment'])) {
            $sentimentColor = match ($metadata['sentiment']) {
                'bullish' => '#28a745',
                'bearish' => '#dc3545',
                default => '#6c757d'
            };
            
            $htmlMessage .= sprintf(
                '<p><strong>Sentiment:</strong> <span style="color: %s; font-weight: bold;">%s</span></p>',
                $sentimentColor,
                htmlspecialchars($metadata['sentiment'])
            );
        }

        return sprintf(
            '<html><body style="font-family: Arial, sans-serif; padding: 20px;">%s</body></html>',
            $htmlMessage
        );
    }
}
