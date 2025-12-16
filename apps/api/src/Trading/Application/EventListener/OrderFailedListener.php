<?php

declare(strict_types=1);

namespace App\Trading\Application\EventListener;

use App\Trading\Domain\Event\OrderFailed;
use App\Audit\Application\Command\LogAuditEntry;
use App\Audit\Domain\ValueObject\AuditAction;
use App\Alert\Application\Command\SendNotification;
use App\Alert\Domain\ValueObject\NotificationChannel;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class OrderFailedListener
{
    public function __construct(
        private MessageBusInterface $commandBus
    ) {
    }

    public function __invoke(OrderFailed $event): void
    {
        // Log audit entry
        $this->commandBus->dispatch(new LogAuditEntry(
            action: AuditAction::ORDER_FAILED,
            userId: $event->userId,
            entityType: 'Order',
            entityId: $event->orderId->toString(),
            metadata: [
                'symbol' => $event->symbol,
                'error_code' => $event->errorCode,
                'error_message' => $event->errorMessage,
                'failed_at' => $event->occurredAt->format(\DateTimeInterface::ATOM)
            ],
            ipAddress: null,
            userAgent: null
        ));

        // Send notification to user
        $this->commandBus->dispatch(new SendNotification(
            userId: $event->userId,
            channels: [NotificationChannel::EMAIL, NotificationChannel::PUSH],
            title: '❌ Order Failed',
            message: sprintf(
                'Your order for %s failed: %s',
                $event->symbol,
                $event->errorMessage
            ),
            metadata: [
                'order_id' => $event->orderId->toString(),
                'symbol' => $event->symbol,
                'error_code' => $event->errorCode
            ]
        ));
    }
}
