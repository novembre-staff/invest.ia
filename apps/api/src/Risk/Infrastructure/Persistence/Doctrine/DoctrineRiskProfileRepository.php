<?php

declare(strict_types=1);

namespace App\Risk\Infrastructure\Persistence\Doctrine;

use App\Identity\Domain\ValueObject\UserId;
use App\Risk\Domain\Model\RiskProfile;
use App\Risk\Domain\Repository\RiskProfileRepositoryInterface;
use App\Risk\Domain\ValueObject\RiskProfileId;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DoctrineRiskProfileRepository extends ServiceEntityRepository implements RiskProfileRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RiskProfile::class);
    }

    public function save(RiskProfile $profile): void
    {
        $this->getEntityManager()->persist($profile);
        $this->getEntityManager()->flush();
    }

    public function findById(RiskProfileId $id): ?RiskProfile
    {
        return $this->find($id->getValue());
    }

    public function findByUserId(UserId $userId): ?RiskProfile
    {
        return $this->findOneBy(['userId' => $userId->getValue()]);
    }

    public function delete(RiskProfile $profile): void
    {
        $this->getEntityManager()->remove($profile);
        $this->getEntityManager()->flush();
    }
}
