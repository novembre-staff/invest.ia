<?php

declare(strict_types=1);

namespace App\Risk\Domain\Repository;

use App\Identity\Domain\ValueObject\UserId;
use App\Risk\Domain\Model\RiskProfile;
use App\Risk\Domain\ValueObject\RiskProfileId;

interface RiskProfileRepositoryInterface
{
    public function save(RiskProfile $profile): void;

    public function findById(RiskProfileId $id): ?RiskProfile;

    public function findByUserId(UserId $userId): ?RiskProfile;

    public function delete(RiskProfile $profile): void;
}
