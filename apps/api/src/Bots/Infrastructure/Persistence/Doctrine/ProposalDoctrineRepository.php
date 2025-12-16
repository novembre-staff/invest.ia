<?php

declare(strict_types=1);

namespace App\Bots\Infrastructure\Persistence\Doctrine;

use App\Bots\Domain\Model\Proposal;
use App\Bots\Domain\Repository\ProposalRepositoryInterface;
use App\Bots\Domain\ValueObject\ProposalId;
use App\Bots\Domain\ValueObject\ProposalStatus;
use App\Identity\Domain\ValueObject\UserId;
use App\Strategy\Domain\ValueObject\StrategyId;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class ProposalDoctrineRepository implements ProposalRepositoryInterface
{
    private EntityRepository $repository;

    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
        $this->repository = $entityManager->getRepository(Proposal::class);
    }

    public function save(Proposal $proposal): void
    {
        $this->entityManager->persist($proposal);
        $this->entityManager->flush();
    }

    public function findById(ProposalId $id): ?Proposal
    {
        return $this->repository->find($id->getValue());
    }

    public function findByUserId(UserId $userId, ?ProposalStatus $status = null, int $limit = 50): array
    {
        $qb = $this->repository->createQueryBuilder('p')
            ->where('p.userId = :userId')
            ->setParameter('userId', $userId->getValue())
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($limit);

        if ($status !== null) {
            $qb->andWhere('p.status = :status')
               ->setParameter('status', $status->value);
        }

        return $qb->getQuery()->getResult();
    }

    public function findByStrategyId(StrategyId $strategyId, ?ProposalStatus $status = null): array
    {
        $qb = $this->repository->createQueryBuilder('p')
            ->where('p.strategyId = :strategyId')
            ->setParameter('strategyId', $strategyId->getValue())
            ->orderBy('p.createdAt', 'DESC');

        if ($status !== null) {
            $qb->andWhere('p.status = :status')
               ->setParameter('status', $status->value);
        }

        return $qb->getQuery()->getResult();
    }

    public function findExpiredPendingProposals(): array
    {
        return $this->repository->createQueryBuilder('p')
            ->where('p.status = :status')
            ->andWhere('p.expiresAt < :now')
            ->setParameter('status', ProposalStatus::PENDING->value)
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->getResult();
    }

    public function countPendingByUserId(UserId $userId): int
    {
        return (int) $this->repository->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.userId = :userId')
            ->andWhere('p.status = :status')
            ->setParameter('userId', $userId->getValue())
            ->setParameter('status', ProposalStatus::PENDING->value)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function delete(Proposal $proposal): void
    {
        $this->entityManager->remove($proposal);
        $this->entityManager->flush();
    }
}
