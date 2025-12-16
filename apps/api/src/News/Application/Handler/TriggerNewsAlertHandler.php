<?php

declare(strict_types=1);

namespace App\News\Application\Handler;

use App\Audit\Application\Command\LogAuditEvent;
use App\Audit\Domain\ValueObject\AuditAction;
use App\News\Application\Command\TriggerNewsAlert;
use App\News\Domain\Event\HighImportanceNewsDetected;
use App\News\Domain\Repository\NewsArticleRepositoryInterface;
use App\News\Domain\ValueObject\NewsArticleId;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class TriggerNewsAlertHandler
{
    public function __construct(
        private readonly NewsArticleRepositoryInterface $newsRepository,
        private readonly MessageBusInterface $eventBus
    ) {}

    public function __invoke(TriggerNewsAlert $command): void
    {
        $articleId = NewsArticleId::fromString($command->articleId);
        $article = $this->newsRepository->findById($articleId);

        if (!$article) {
            throw new \DomainException('News article not found.');
        }

        // Vérifier si l'article mérite une alerte
        if (!$article->isHighImpact() && $article->getImportanceScore()->getScore() < 70) {
            throw new \DomainException('Article does not meet alert criteria.');
        }

        // Dispatch event pour notification
        $this->eventBus->dispatch(new HighImportanceNewsDetected(
            articleId: $article->getId(),
            title: $article->getTitle(),
            summary: $article->getSummary(),
            importanceScore: $article->getImportanceScore()->getScore(),
            sentiment: $article->getSentiment(),
            relatedSymbols: $article->getRelatedSymbols(),
            publishedAt: $article->getPublishedAt(),
            reason: $command->reason
        ));
    }
}
