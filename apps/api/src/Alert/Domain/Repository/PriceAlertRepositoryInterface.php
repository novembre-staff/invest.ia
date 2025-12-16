<?php

declare(strict_types=1);

namespace App\Alert\Domain\Repository;

use App\Alert\Domain\Model\PriceAlert;
use App\Alert\Domain\ValueObject\AlertStatus;
use App\Alert\Domain\ValueObject\AlertType;
use App\Alert\Domain\ValueObject\PriceAlertId;
use App\Identity\Domain\ValueObject\UserId;

interface PriceAlertRepositoryInterface
{
    public function save(PriceAlert $alert): void;

    public function findById(PriceAlertId $id): ?PriceAlert;

    /**
     * @return PriceAlert[]
     */
    public function findByUserId(UserId $userId): array;

    /**
     * @return PriceAlert[]
     */
    public function findActiveByUserId(UserId $userId): array;

    /**
     * @return PriceAlert[]
     */
    public function findActiveBySymbol(string $symbol): array;

    /**
     * @return PriceAlert[]
     */
    public function findActiveByType(AlertType $type): array;

    /**
     * @return PriceAlert[]
     */
    public function findAllActive(): array;

    public function delete(PriceAlert $alert): void;
}
