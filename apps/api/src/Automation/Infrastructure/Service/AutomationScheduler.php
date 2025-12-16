<?php

declare(strict_types=1);

namespace App\Automation\Infrastructure\Service;

use App\Automation\Domain\Model\Automation;
use App\Automation\Domain\Repository\AutomationRepositoryInterface;
use App\Automation\Domain\Service\AutomationExecutorInterface;
use App\Automation\Domain\ValueObject\AutomationType;
use Psr\Log\LoggerInterface;

class AutomationScheduler
{
    public function __construct(
        private AutomationRepositoryInterface $automationRepository,
        private AutomationExecutorInterface $executor,
        private LoggerInterface $logger
    ) {
    }

    /**
     * Process all automations that are due for execution
     */
    public function processDueAutomations(): int
    {
        $now = new \DateTimeImmutable();
        $dueAutomations = $this->automationRepository->findDueForExecution($now);
        
        $this->logger->info('Processing due automations', [
            'count' => count($dueAutomations),
            'timestamp' => $now->format('Y-m-d H:i:s')
        ]);

        $executedCount = 0;

        foreach ($dueAutomations as $automation) {
            try {
                if ($this->executor->canExecute($automation)) {
                    $investedAmount = $this->executor->execute($automation);
                    
                    $automation->recordExecution($investedAmount);
                    $this->automationRepository->save($automation);
                    
                    $executedCount++;
                    
                    $this->logger->info('Automation executed successfully', [
                        'automation_id' => $automation->getId()->toString(),
                        'type' => $automation->getType()->value,
                        'invested_amount' => $investedAmount,
                        'execution_count' => $automation->getExecutionCount()
                    ]);
                } else {
                    $this->logger->warning('Automation cannot be executed', [
                        'automation_id' => $automation->getId()->toString(),
                        'type' => $automation->getType()->value
                    ]);
                }
            } catch (\Exception $e) {
                $this->logger->error('Automation execution failed', [
                    'automation_id' => $automation->getId()->toString(),
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                $automation->markAsFailed();
                $this->automationRepository->save($automation);
            }
        }

        return $executedCount;
    }

    /**
     * Get automation statistics
     */
    public function getStatistics(): array
    {
        $activeAutomations = $this->automationRepository->findActiveAutomations();
        
        $stats = [
            'total_active' => count($activeAutomations),
            'by_type' => [],
            'next_executions' => []
        ];

        foreach ($activeAutomations as $automation) {
            $type = $automation->getType()->value;
            $stats['by_type'][$type] = ($stats['by_type'][$type] ?? 0) + 1;

            if ($automation->getNextExecutionAt() !== null) {
                $stats['next_executions'][] = [
                    'automation_id' => $automation->getId()->toString(),
                    'name' => $automation->getName(),
                    'type' => $type,
                    'next_execution_at' => $automation->getNextExecutionAt()->format('Y-m-d H:i:s')
                ];
            }
        }

        // Sort next executions by time
        usort($stats['next_executions'], function ($a, $b) {
            return strcmp($a['next_execution_at'], $b['next_execution_at']);
        });

        return $stats;
    }
}
