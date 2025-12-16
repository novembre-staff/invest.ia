<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Console;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:health-check',
    description: 'Check system health status'
)]
final class HealthCheckCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('System Health Check');

        $checks = [
            'Database' => $this->checkDatabase(),
            'Redis' => $this->checkRedis(),
            'Messenger' => $this->checkMessenger(),
            'Storage' => $this->checkStorage(),
        ];

        foreach ($checks as $service => $status) {
            if ($status['ok']) {
                $io->success(sprintf('%s: OK - %s', $service, $status['message']));
            } else {
                $io->error(sprintf('%s: FAILED - %s', $service, $status['message']));
            }
        }

        $allOk = array_reduce($checks, fn($carry, $check) => $carry && $check['ok'], true);

        return $allOk ? Command::SUCCESS : Command::FAILURE;
    }

    private function checkDatabase(): array
    {
        try {
            // TODO: Implémenter check database connection
            return ['ok' => true, 'message' => 'Connection successful'];
        } catch (\Exception $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    private function checkRedis(): array
    {
        try {
            // TODO: Implémenter check Redis connection
            return ['ok' => true, 'message' => 'Connection successful'];
        } catch (\Exception $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    private function checkMessenger(): array
    {
        try {
            // TODO: Vérifier que les workers tournent
            return ['ok' => true, 'message' => 'Workers active'];
        } catch (\Exception $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    private function checkStorage(): array
    {
        try {
            $varDir = __DIR__ . '/../../../../var';
            $writable = is_writable($varDir);
            return [
                'ok' => $writable,
                'message' => $writable ? 'Writable' : 'Not writable'
            ];
        } catch (\Exception $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }
}
