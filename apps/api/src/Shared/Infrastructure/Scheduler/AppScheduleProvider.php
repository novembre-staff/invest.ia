<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Scheduler;

use App\Shared\Application\Command\AnalyzeRecentNews;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

#[AsSchedule('default')]
final class AppScheduleProvider implements ScheduleProviderInterface
{
    public function getSchedule(): Schedule
    {
        return (new Schedule())
            // Analyse des actualités toutes les 15 minutes
            ->add(
                RecurringMessage::every(
                    '15 minutes',
                    new AnalyzeRecentNews(maxArticles: 50, hoursBack: 1)
                )
            )
            
            // TODO: Ajouter d'autres tâches planifiées
            // - Synchronisation portfolio Binance (5 min)
            // - Vérification limites risque (1 min)
            // - Cleanup logs anciens (daily)
            // - Export rapports (daily)
        ;
    }
}
