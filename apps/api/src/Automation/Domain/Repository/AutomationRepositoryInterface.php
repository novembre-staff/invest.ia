<?php

declare(strict_types=1);

namespace App\Automation\Domain\Repository;

use App\Automation\Domain\Model\Automation;
use App\Automation\Domain\ValueObject\AutomationId;
use App\Automation\Domain\ValueObject\AutomationStatus;
use App\Identity\Domain\ValueObject\UserId;

interface AutomationRepositoryInterface
{
    public function save(Automation $automation): void;

    public function findById(AutomationId $id): ?Automation;

    public function findByUserId(UserId $userId): array;

    public function findByStatus(AutomationStatus $status): array;

    public function findActiveAutomations(): array;

    public function findDueForExecution(\DateTimeImmutable $now): array;

    public function delete(Automation $automation): void;
}
