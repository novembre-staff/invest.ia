<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Console;

use App\Shared\Application\Command\AnalyzeRecentNews;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

#[AsCommand(
    name: 'app:news:analyze-recent',
    description: 'Analyze sentiment of recent news articles'
)]
final class AnalyzeRecentNewsCommand extends Command
{
    public function __construct(
        private readonly MessageBusInterface $commandBus
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'max',
                'm',
                InputOption::VALUE_OPTIONAL,
                'Maximum number of articles to analyze',
                50
            )
            ->addOption(
                'hours',
                null,
                InputOption::VALUE_OPTIONAL,
                'Look back N hours (null = all unanalyzed)',
                6
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('News Sentiment Analysis - Scheduled Task');

        $max = (int) $input->getOption('max');
        $hours = $input->getOption('hours') !== null ? (int) $input->getOption('hours') : null;

        $io->info(sprintf(
            'Analyzing up to %d articles from the last %s hours...',
            $max,
            $hours ?? 'all'
        ));

        try {
            $envelope = $this->commandBus->dispatch(
                new AnalyzeRecentNews(maxArticles: $max, hoursBack: $hours)
            );

            $handledStamp = $envelope->last(HandledStamp::class);
            $result = $handledStamp?->getResult();

            if ($result) {
                $io->success(sprintf(
                    'Analysis completed: %d articles found, %d dispatched, %d errors',
                    $result['total_found'],
                    $result['dispatched'],
                    $result['errors']
                ));

                if ($result['errors'] > 0) {
                    $io->warning('Some articles failed to dispatch. Check logs for details.');
                }
            } else {
                $io->success('Analysis dispatched successfully');
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error(sprintf('Failed to analyze news: %s', $e->getMessage()));
            return Command::FAILURE;
        }
    }
}
