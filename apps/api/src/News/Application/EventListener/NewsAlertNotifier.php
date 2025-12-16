<?php

declare(strict_types=1);

namespace App\News\Application\EventListener;

use App\Alert\Application\Command\CreateAlert;
use App\News\Domain\Event\HighImportanceNewsDetected;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Listener qui crée automatiquement des alertes pour les news importantes
 */
#[AsEventListener(event: HighImportanceNewsDetected::class)]
class NewsAlertNotifier
{
    public function __construct(
        private readonly MessageBusInterface $commandBus
    ) {}

    public function __invoke(HighImportanceNewsDetected $event): void
    {
        // Pour chaque symbole mentionné, on pourrait créer une alerte
        // ou notifier les utilisateurs qui suivent ces symboles
        
        // Exemple: Envoyer notification push/email aux utilisateurs concernés
        // Cette logique dépend du système de notification global
        
        // Log pour traçabilité
        error_log(sprintf(
            'High importance news detected: %s (Score: %d, Symbols: %s)',
            $event->title,
            $event->importanceScore,
            implode(', ', $event->relatedSymbols)
        ));
    }
}
