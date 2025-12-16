<?php

declare(strict_types=1);

namespace App\Trading\Application\EventListener;

use App\Trading\Domain\Event\OrderCancelled;
use App\Audit\Application\Command\LogAuditEntry;
use App\Audit\Domain\ValueObject\AuditAction;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class OrderCancelledListener
{
    public function __construct(
        private MessageBusInterface $commandBus
    ) {
    }

    public function __invoke(OrderCancelled $event): void
    {
        // Log audit entry
        $this->commandBus->dispatch(new LogAuditEntry(
            action: AuditAction::ORDER_CANCELLED,
            userId: $event->userId,
            entityType: 'Order',
            entityId: $event->orderId->toString(),
            metadata: [
                'symbol' => $event->symbol,
                'reason' => $event->reason,
                'cancelled_at' => $event->occurredAt->format(\DateTimeInterface::ATOM)
            ],
            ipAddress: null,
            userAgent: null
        ));
    }
}
