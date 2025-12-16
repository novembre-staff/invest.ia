<?php

declare(strict_types=1);

namespace App\News\Infrastructure\Persistence\Doctrine;

use App\News\Domain\Model\NewsArticle;
use App\News\Domain\Repository\NewsArticleRepositoryInterface;
use App\News\Domain\ValueObject\NewsArticleId;
use App\News\Domain\ValueObject\NewsCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<NewsArticle>
 */
class DoctrineNewsArticleRepository extends ServiceEntityRepository implements NewsArticleRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NewsArticle::class);
    }

    public function save(NewsArticle $article): void
    {
        $this->getEntityManager()->persist($article);
        $this->getEntityManager()->flush();
    }

    public function findById(NewsArticleId $id): ?NewsArticle
    {
        return $this->find($id->getValue());
    }

    /**
     * @return NewsArticle[]
     */
    public function findRecent(int $limit = 50, ?NewsCategory $category = null): array
    {
        $qb = $this->createQueryBuilder('n')
            ->orderBy('n.publishedAt', 'DESC')
            ->setMaxResults($limit);

        if ($category !== null) {
            $qb->andWhere('n.category = :category')
                ->setParameter('category', $category->value);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param string[] $symbols
     * @return NewsArticle[]
     */
    public function findBySymbols(array $symbols, int $limit = 20): array
    {
        // PostgreSQL JSONB query to find articles with any of the symbols
        $qb = $this->createQueryBuilder('n');

        $conditions = [];
        foreach ($symbols as $index => $symbol) {
            $conditions[] = "JSONB_CONTAINS(n.relatedSymbols, :symbol{$index}) = true";
            $qb->setParameter("symbol{$index}", json_encode([$symbol]));
        }

        return $qb->where($qb->expr()->orX(...$conditions))
            ->orderBy('n.publishedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return NewsArticle[]
     */
    public function findHighImportance(int $minScore = 75, int $limit = 20): array
    {
        return $this->createQueryBuilder('n')
            ->where('n.importanceScore >= :minScore')
            ->setParameter('minScore', $minScore)
            ->orderBy('n.importanceScore', 'DESC')
            ->addOrderBy('n.publishedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function existsBySourceUrl(string $sourceUrl): bool
    {
        $count = $this->createQueryBuilder('n')
            ->select('COUNT(n.id)')
            ->where('n.sourceUrl = :sourceUrl')
            ->setParameter('sourceUrl', $sourceUrl)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }
}
