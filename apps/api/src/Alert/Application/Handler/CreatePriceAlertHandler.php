<?php

declare(strict_types=1);

namespace App\Alert\Application\Handler;

use App\Alert\Application\Command\CreatePriceAlert;
use App\Alert\Application\DTO\PriceAlertDTO;
use App\Alert\Domain\Model\PriceAlert;
use App\Alert\Domain\Repository\PriceAlertRepositoryInterface;
use App\Alert\Domain\ValueObject\AlertCondition;
use App\Alert\Domain\ValueObject\AlertType;
use App\Alert\Domain\ValueObject\NotificationChannel;
use App\Identity\Domain\ValueObject\UserId;

final readonly class CreatePriceAlertHandler
{
    public function __construct(
        private PriceAlertRepositoryInterface $alertRepository
    ) {
    }

    public function __invoke(CreatePriceAlert $command): PriceAlertDTO
    {
        $userId = UserId::fromString($command->userId);
        $type = AlertType::from($command->type);
        
        // Parse notification channels
        $channels = array_map(
            fn(string $channel) => NotificationChannel::from($channel),
            $command->notificationChannels
        );

        // Create condition based on type
        $condition = $this->createCondition($type, $command->targetValue);

        // Parse expiration date if provided
        $expiresAt = $command->expiresAt !== null 
            ? new \DateTimeImmutable($command->expiresAt)
            : null;

        // Create alert
        $alert = PriceAlert::create(
            $userId,
            $type,
            $command->symbol,
            $condition,
            $channels,
            $command->message,
            $expiresAt
        );

        $this->alertRepository->save($alert);

        return PriceAlertDTO::fromDomain($alert);
    }

    private function createCondition(AlertType $type, float $targetValue): AlertCondition
    {
        return match($type) {
            AlertType::PRICE_ABOVE => AlertCondition::priceAbove($targetValue),
            AlertType::PRICE_BELOW => AlertCondition::priceBelow($targetValue),
            AlertType::PRICE_CHANGE_PERCENT => AlertCondition::percentChange($targetValue),
            AlertType::VOLUME_SPIKE => AlertCondition::volumeSpike($targetValue),
            AlertType::PORTFOLIO_VALUE => AlertCondition::portfolioValue($targetValue),
            AlertType::POSITION_PROFIT_TARGET => AlertCondition::priceAbove($targetValue),
            AlertType::POSITION_STOP_LOSS => AlertCondition::priceBelow($targetValue),
        };
    }
}
