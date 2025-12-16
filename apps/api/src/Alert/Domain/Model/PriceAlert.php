<?php

declare(strict_types=1);

namespace App\Alert\Domain\Model;

use App\Alert\Domain\ValueObject\AlertCondition;
use App\Alert\Domain\ValueObject\AlertStatus;
use App\Alert\Domain\ValueObject\AlertType;
use App\Alert\Domain\ValueObject\NotificationChannel;
use App\Alert\Domain\ValueObject\PriceAlertId;
use App\Identity\Domain\ValueObject\UserId;

class PriceAlert
{
    private PriceAlertId $id;
    private UserId $userId;
    private AlertType $type;
    private ?string $symbol;
    private AlertCondition $condition;
    private AlertStatus $status;
    /** @var NotificationChannel[] */
    private array $notificationChannels;
    private ?string $message;
    private ?\DateTimeImmutable $triggeredAt = null;
    private ?\DateTimeImmutable $expiresAt;
    private \DateTimeImmutable $createdAt;

    public function __construct(
        PriceAlertId $id,
        UserId $userId,
        AlertType $type,
        ?string $symbol,
        AlertCondition $condition,
        array $notificationChannels,
        ?string $message = null,
        ?\DateTimeImmutable $expiresAt = null
    ) {
        $this->validateSymbolRequirement($type, $symbol);
        $this->validateNotificationChannels($notificationChannels);

        $this->id = $id;
        $this->userId = $userId;
        $this->type = $type;
        $this->symbol = $symbol;
        $this->condition = $condition;
        $this->status = AlertStatus::ACTIVE;
        $this->notificationChannels = $notificationChannels;
        $this->message = $message;
        $this->expiresAt = $expiresAt;
        $this->createdAt = new \DateTimeImmutable();
    }

    public static function create(
        UserId $userId,
        AlertType $type,
        ?string $symbol,
        AlertCondition $condition,
        array $notificationChannels,
        ?string $message = null,
        ?\DateTimeImmutable $expiresAt = null
    ): self {
        return new self(
            PriceAlertId::generate(),
            $userId,
            $type,
            $symbol,
            $condition,
            $notificationChannels,
            $message,
            $expiresAt
        );
    }

    public function trigger(\DateTimeImmutable $triggeredAt = null): void
    {
        if (!$this->status->isActive()) {
            throw new \DomainException('Only active alerts can be triggered');
        }

        $this->status = AlertStatus::TRIGGERED;
        $this->triggeredAt = $triggeredAt ?? new \DateTimeImmutable();
    }

    public function cancel(): void
    {
        if (!$this->status->canBeCancelled()) {
            throw new \DomainException('Only active alerts can be cancelled');
        }

        $this->status = AlertStatus::CANCELLED;
    }

    public function expire(): void
    {
        if (!$this->status->isActive()) {
            return;
        }

        $this->status = AlertStatus::EXPIRED;
    }

    public function isExpired(\DateTimeImmutable $now = null): bool
    {
        if ($this->expiresAt === null) {
            return false;
        }

        $now = $now ?? new \DateTimeImmutable();
        return $this->expiresAt < $now;
    }

    public function shouldEvaluate(): bool
    {
        if (!$this->status->isActive()) {
            return false;
        }

        if ($this->isExpired()) {
            $this->expire();
            return false;
        }

        return true;
    }

    public function matches(AlertType $type, ?string $symbol = null): bool
    {
        if ($this->type !== $type) {
            return false;
        }

        if ($symbol !== null && $this->symbol !== $symbol) {
            return false;
        }

        return true;
    }

    private function validateSymbolRequirement(AlertType $type, ?string $symbol): void
    {
        if ($type->requiresSymbol() && $symbol === null) {
            throw new \InvalidArgumentException(
                sprintf('Alert type %s requires a symbol', $type->value)
            );
        }
    }

    private function validateNotificationChannels(array $channels): void
    {
        if (empty($channels)) {
            throw new \InvalidArgumentException('At least one notification channel is required');
        }

        foreach ($channels as $channel) {
            if (!$channel instanceof NotificationChannel) {
                throw new \InvalidArgumentException('Invalid notification channel');
            }
        }
    }

    // Getters
    public function getId(): PriceAlertId
    {
        return $this->id;
    }

    public function getUserId(): UserId
    {
        return $this->userId;
    }

    public function getType(): AlertType
    {
        return $this->type;
    }

    public function getSymbol(): ?string
    {
        return $this->symbol;
    }

    public function getCondition(): AlertCondition
    {
        return $this->condition;
    }

    public function getStatus(): AlertStatus
    {
        return $this->status;
    }

    public function getNotificationChannels(): array
    {
        return $this->notificationChannels;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function getTriggeredAt(): ?\DateTimeImmutable
    {
        return $this->triggeredAt;
    }

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
